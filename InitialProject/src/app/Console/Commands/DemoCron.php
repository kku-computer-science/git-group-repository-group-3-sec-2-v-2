<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Book;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DemoCron extends Command
{
    /**
     * ชื่อและลายเซ็นของคำสั่ง console
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * คำอธิบายของคำสั่ง console
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * สร้างอินสแตนซ์คำสั่งใหม่
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * รันคำสั่ง console
     *
     * @return mixed
     */
    public function handle()
    {
        // บันทึกข้อมูลเพื่อระบุว่า cron job ทำงาน
        #Log::info("Cron is working fine!");

        // ดึงข้อมูลผู้ใช้
        $data = User::find(16);

        // วนลูปผ่านผู้ใช้แต่ละคน
        foreach ($data as $name) {
            // ดึงตัวอักษรแรกของชื่อและนามสกุล
            $fname = substr($name['fname_en'], 0, 1);
            $lname = $name['lname_en'];
            $id    = $name['id'];

            // ทำการเรียก API เพื่อดึงข้อมูลผู้แต่ง
            $url = Http::get('https://api.elsevier.com/content/search/scopus?', [
                'query' => "AUTHOR-NAME(" . "$lname" . "," . "$fname" . ")",
                'apikey' => '6ab3c2a01c29f0e36b00c8fa1d013f83',
            ])->json();

            // ดึงเนื้อหาและลิงก์จากการตอบกลับของ API
            $content = $url["search-results"]["entry"];
            $links = $url["search-results"]["link"];

            // วนลูปเพื่อดึงหน้าผลลัพธ์ทั้งหมด
            do {
                $ref = 'prev';
                foreach ($links as $link) {
                    if ($link['@ref'] == 'next') {
                        $link2 = $link['@href'];
                        $link2 = Http::get("$link2")->json();
                        $links = $link2["search-results"]["link"];
                        $nextcontent = $link2["search-results"]["entry"];
                        foreach ($nextcontent as $item) {
                            array_push($content, $item);
                        }
                    }
                }
            } while ($ref != 'prev');

            // ประมวลผลแต่ละ paper ในเนื้อหา
            foreach ($content as $item) {
                if (array_key_exists('error', $item)) {
                    continue;
                } else {
                    // ตรวจสอบว่า paper นี้มีอยู่ในฐานข้อมูลหรือไม่
                    if (Paper::where('paper_name', '=', $item['dc:title'])->first() == null) {
                        // ดึงรายละเอียด paper และบันทึกลงฐานข้อมูล
                        $scoid = $item['dc:identifier'];
                        $scoid = explode(":", $scoid);
                        $scoid = $scoid[1];

                        $all = Http::get("https://api.elsevier.com/content/abstract/scopus_id/" . $scoid . "?filed=authors&apiKey=6ab3c2a01c29f0e36b00c8fa1d013f83&httpAccept=application%2Fjson");

                        $paper = new Paper;
                        $paper->paper_name = $item['dc:title'];
                        $paper->paper_type = $item['prism:aggregationType'];
                        $paper->paper_subtype = $item['subtypeDescription'];
                        $paper->paper_sourcetitle = $item['prism:publicationName'];
                        $paper->paper_url = $item['link'][2]['@href'];
                        $date = Carbon::parse($item['prism:coverDate'])->format('Y');
                        $paper->paper_yearpub = $date;

                        if (array_key_exists('prism:volume', $item)) {
                            $paper->paper_volume = $item['prism:volume'];
                        } else {
                            $paper->paper_volume = null;
                        }
                        if (array_key_exists('prism:issueIdentifier', $item)) {
                            $paper->paper_issue = $item['prism:issueIdentifier'];
                        } else {
                            $paper->paper_issue = null;
                        }

                        $paper->paper_citation = $item['citedby-count'];
                        $paper->paper_page = $item['prism:pageRange'];

                        if (array_key_exists('prism:doi', $item)) {
                            $paper->paper_doi = $item['prism:doi'];
                        } else {
                            $paper->paper_doi = null;
                        }

                        if (array_key_exists('item', $all['abstracts-retrieval-response'])) {
                            if (array_key_exists('xocs:meta', $all['abstracts-retrieval-response']['item'])) {
                                if (array_key_exists('xocs:funding-text', $all['abstracts-retrieval-response']['item']['xocs:meta']['xocs:funding-list'])) {
                                    $funder = $all['abstracts-retrieval-response']['item']['xocs:meta']['xocs:funding-list']['xocs:funding-text'];
                                    $paper->paper_funder = json_encode($funder);
                                } else {
                                    $paper->paper_funder = null;
                                }
                            } else {
                                $paper->paper_funder = null;
                            }

                            $paper->abstract = $all['abstracts-retrieval-response']['item']['bibrecord']['head']['abstracts'];

                            if (array_key_exists('author-keywords', $all['abstracts-retrieval-response']['item']['bibrecord']['head']['citation-info'])) {
                                $key = $all['abstracts-retrieval-response']['item']['bibrecord']['head']['citation-info']['author-keywords']['author-keyword'];
                                $paper->keyword = json_encode($key);
                            } else {
                                $paper->keyword = null;
                            }
                        } else {
                            $paper->paper_funder = null;
                            $paper->abstract = null;
                            $paper->keyword = null;
                        }

                        $paper->save();

                        $source = Source_data::findOrFail(1);
                        $paper->source()->sync($source);

                        $all_au = $all['abstracts-retrieval-response']['authors']['author'];

                        $x = 1;
                        $length = count($all_au);
                        foreach ($all_au as $i) {
                            if (User::where([['fname_en', '=', $i['ce:given-name']], ['lname_en', '=', $i['ce:surname']]])->orWhere([[DB::raw("concat(left(fname_en,1),'.')"), '=', $i['ce:given-name']], ['lname_en', '=', $i['ce:surname']]])->first() == null) {
                                if (Author::where([['author_fname', '=', $i['ce:given-name']], ['author_lname', '=', $i['ce:surname']]])->first() == null) {
                                    $author = new Author;
                                    $author->author_fname = $i['ce:given-name'];
                                    $author->author_lname = $i['ce:surname'];
                                    $author->save();
                                    if ($x === 1) {
                                        $paper->author()->attach($author, ['author_type' => 1]);
                                    } else if ($x === $length) {
                                        $paper->author()->attach($author, ['author_type' => 3]);
                                    } else {
                                        $paper->author()->attach($author, ['author_type' => 2]);
                                    }
                                } else {
                                    $author = Author::where([['author_fname', '=', $i['ce:given-name']], ['author_lname', '=', $i['ce:surname']]])->first();
                                    $authorid = $author->id;
                                    if ($x === 1) {
                                        $paper->author()->attach($authorid, ['author_type' => 1]);
                                    } else if ($x === $length) {
                                        $paper->author()->attach($authorid, ['author_type' => 3]);
                                    } else {
                                        $paper->author()->attach($authorid, ['author_type' => 2]);
                                    }
                                }
                            } else {
                                $us = User::where([['fname_en', '=', $i['ce:given-name']], ['lname_en', '=', $i['ce:surname']]])->orWhere([[DB::raw("concat(left(fname_en,1),'.')"), '=', $i['ce:given-name']], ['lname_en', '=', $i['ce:surname']]])->first();
                                if ($x === 1) {
                                    $paper->teacher()->attach($us, ['author_type' => 1]);
                                } else if ($x === $length) {
                                    $paper->teacher()->attach($us, ['author_type' => 3]);
                                } else {
                                    $paper->teacher()->attach($us, ['author_type' => 2]);
                                }
                            }
                            $x++;
                        }
                    } else {
                        $paper = Paper::where('paper_name', '=', $item['dc:title'])->first();
                        $paper->paper_citation = $item['citedby-count'];
                        $paper->update();
                    }
                }
            }
        }
    }
}
