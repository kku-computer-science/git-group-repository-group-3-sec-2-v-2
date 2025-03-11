<?php

namespace App\Http\Controllers;

use App\Exports\ExportPaper;
use App\Exports\ExportUser;
use App\Exports\UsersExport;
use App\Models\Author;
use App\Models\AuthorOfPaper;
use App\Models\UserPaper;
use App\Models\Paper;
use App\Models\Source_data;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\DB;
use App\Traits\LogsUserActions;

class PaperController extends Controller
{
    use LogsUserActions;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $id = auth()->user()->id;
        //$papers=User::find($id)->paper()->latest()->paginate(5);

        //$papers = Paper::with('teacher')->get();
        /*$user = User::find($id);
        $papers = $user->paper()->get();
        return response()->json($papers);*/
        if (auth()->user()->hasRole('admin') or auth()->user()->hasRole('staff')) {
            $papers = Paper::with('teacher', 'author')->orderBy('paper_yearpub', 'desc')->get();
        } else {
            $papers = Paper::with('teacher', 'author')->whereHas('teacher', function ($query) use ($id) {
                $query->where('users.id', '=', $id);
            })->orderBy('paper_yearpub', 'desc')->get();
        }

        // $papers = Paper::with('teacher','author')->whereHas('teacher', function($query) use($id) {
        //     $query->where('users.id', '=', $id);
        //  })->paginate(10);
        //return $papers;
        //return response()->json($papers);
    return view('papers.index', compact('papers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $source = Source_data::all();
        $users = User::role(['teacher', 'student'])->get();
        return view('papers.create', compact('source', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'paper_name' => 'required|unique:papers,paper_name',
            'paper_type' => 'required',
            'paper_sourcetitle' => 'required',
            // 'paper_url' => 'required',
            'paper_yearpub' => 'required',
            'paper_volume' => 'required',
            //'paper_issue' => 'required',
            //'paper_citation' => 'required',
            //'paper_page' => 'required',
            'paper_doi' => 'required',
        ]);
        $input = $request->except(['_token']);

        $key = $input['keyword'];
        $key = explode(', ', $key);
        //$result['$'] = $v;
        $myNewArray = [];
        foreach ($key as $val) {

            $a['$'] = $val;
            array_push($myNewArray, $a);
        }
        //$input['keyword'] = json_encode($myNewArray);
        $input['keyword'] = $myNewArray;
        //return $input['keyword'];
        //$input['keyword'] = 
        $paper = Paper::create($input);
        foreach ($request->cat as $key => $value) {
            $paper->source()->attach($value);
        }
        //$x = 1;
        //$length = count($request->moreFields);
        //return $request->pos[1];
        foreach ($request->moreFields as $key => $value) {
            if ($value['userid'] != null) {
                // if ($x === 1) {
                //     $paper->teacher()->attach($value, ['author_type' => 1]);
                // } else if ($x === $length) {
                //     $paper->teacher()->attach($value, ['author_type' => 3]);
                // } else {
                //     $paper->teacher()->attach($value, ['author_type' => 2]);
                // }
                //$paper->user()->attach($value);
            }
            $paper->teacher()->attach($value, ['author_type' => $request->pos[$key]]);
            //$x++;
        }

        //$paper->author()->detach();
        $x = 1;

        if (isset($input['fname'][0]) and (!empty($input['fname'][0]))) {
            $length = count($request->input('fname'));
            foreach ($request->input('fname') as $key => $value) {
                $data['fname'] = $input['fname'][$key];
                $data['lname'] = $input['lname'][$key];
                if (Author::where([['author_fname', '=', $data['fname']], ['author_lname', '=', $data['lname']]])->first() == null) {

                    $author = new Author;
                    $author->author_fname = $data['fname'];
                    $author->author_lname = $data['lname'];
                    $author->save();
                    // if ($x === 1) {
                    //     $paper->author()->attach($author, ['author_type' => 1]);
                    // } else if ($x === $length) {
                    //     $paper->author()->attach($author, ['author_type' => 3]);
                    // } else {
                    //     $paper->author()->attach($author, ['author_type' => 2]);
                    // }
                    $paper->author()->attach($author, ['author_type' => $request->pos2[$key]]);
                } else {
                    $author = Author::where([['author_fname', '=', $data['fname']], ['author_lname', '=', $data['lname']]])->first();
                    $authorid = $author->id;
                    // if ($x === 1) {
                    //     $paper->author()->attach($authorid, ['author_type' => 1]);
                    // } else if ($x === $length) {
                    //     $paper->author()->attach($authorid, ['author_type' => 3]);
                    // } else {
                    //     $paper->author()->attach($authorid, ['author_type' => 2]);
                    // }
                    $paper->author()->attach($authorid, ['author_type' => $request->pos2[$key]]);
                }
                $x++;
            }
        }

        // Log the paper creation
        $this->logCreate('paper', $paper->id, [
            'paper_name' => $paper->paper_name,
            'paper_type' => $paper->paper_type,
            'paper_sourcetitle' => $paper->paper_sourcetitle,
            'paper_yearpub' => $paper->paper_yearpub,
            'paper_volume' => $paper->paper_volume,
            'paper_doi' => $paper->paper_doi
        ]);

        return redirect()->route('papers.index')
            ->with('success', 'papers created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    // public function show(Paper $paper)
    // {
    //     $k = collect($paper['keyword']);
    //     $val = $k->implode('$', ', ');
    //     $paper['keyword'] = $val;
    //     return view('papers.show', compact('paper'));
    // }

    public function show($id, Request $request)
    {
        $userId = $request->query('user_id'); // รับ user_id จาก query string
        $userimg = DB::table('users')
            ->where('id', $userId)
            ->select('picture', 'fname_en', 'lname_en')
            ->first();

        $paper = Paper::findOrFail($id);

        // Log this as a detailed "Call Paper" action
        if (auth()->check()) {
            $paperData = [
                'paper_id' => $paper->id,
                'paper_name' => $paper->paper_name,
                'paper_type' => $paper->paper_type,
                'paper_sourcetitle' => $paper->paper_sourcetitle ?? '',
                'paper_year' => $paper->paper_yearpub ?? '',
                'paper_doi' => $paper->paper_doi ?? '',
                'search_params' => $request->query(),
                'action_type' => 'Call Paper',
                'referrer' => $request->headers->get('referer')
            ];
            
            // Log with our activity logging trait
            $this->logUserAction('call', 'paper', $paper->id, $paperData);
            
            // Also create a detailed activity log
            \App\Models\ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'Paper View',
                'description' => 'Viewed: ' . $paper->paper_name . ' (ID: ' . $paper->id . ')',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        // ดึงรายชื่อผู้เขียนจาก author_of_papers
        $authors = DB::table('author_of_papers')
            ->join('authors', 'author_of_papers.author_id', '=', 'authors.id')
            ->where('author_of_papers.paper_id', $id)
            ->select(
                'authors.id as author_id',
                'authors.author_fname',
                'authors.author_lname',
                DB::raw("NULL as picture"), // กรณีไม่มีฟิลด์ picture ใน authors
                DB::raw("'author' as type")
            )
            ->union(
                DB::table('user_papers')
                    ->join('users', 'user_papers.user_id', '=', 'users.id')
                    ->where('user_papers.paper_id', $id)
                    ->select(
                        'users.id as author_id',
                        'users.fname_en as author_fname',
                        'users.lname_en as author_lname',
                        'users.picture',
                        DB::raw("'user' as type")
                    )
            )
            ->get();

        return view('paperdetail', compact('userimg', 'paper', 'authors'));
    }


    // public function show($id)
    // {
    //     // ค้นหาข้อมูล Paper ตาม Paper ID ที่ส่งมา
    //     $paper = Paper::with('authors')->findOrFail($id); // ดึงข้อมูล Paper พร้อมกับข้อมูล Authors

    //     // ค้นหาผู้ใช้ (นักวิจัย) ที่เกี่ยวข้องกับ Paper นี้
    //     $res = $paper->authors; // $paper->authors จะดึงข้อมูลผู้เขียนที่เกี่ยวข้องกับ Paper นี้

    //     // ดึงข้อมูล teachers ทั้งหมดที่มี role เป็น 'teacher'
    //     $teachers = User::role('teacher')->get();


    //     // ส่งข้อมูลไปยัง view 'paperdetail'
    //     return view('paperdetail', compact('res', 'teachers', 'paper'));
    // }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $id =  decrypt($id);
            //return abort(404, "page not found");
            $paper = Paper::find($id);
            //$paper['keyword'] = json_decode($paper['keyword'],true);
            //$paper['keyword'] = json_encode($paper['keyword']);
            $k = collect($paper['keyword']);
            $val = $k->implode('$', ', ');
            $paper['keyword'] = $val;
            //return $val;
            //$researchProject = ResearchProject::find($researchProject->id);
            $this->authorize('update', $paper);
            //$source = Source_data::all();
            $sources = Source_data::pluck('source_name', 'source_name')->all();
            $paperSource = $paper->source->pluck('source_name', 'source_name')->all();
            $users = User::role(['teacher', 'student'])->get();
            return view('papers.edit', compact('paper', 'users', 'paperSource', 'sources'));
        } catch (DecryptException   $e) {
            return abort(404, "page not found");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Paper $paper)
    {
        $this->validate($request, [
            //'paper_name' => 'required|unique:papers,paper_name',
            'paper_type' => 'required',
            'paper_sourcetitle' => 'required',
            // 'paper_url' => 'required',
            //'paper_yearpub' => 'required',
            'paper_volume' => 'required',
            'paper_issue' => 'required',
            'paper_citation' => 'required',
            'paper_page' => 'required',
            // 'paper_doi' => 'required',
        ]);
        $input = $request->except(['_token']);
        $key = $input['keyword'];
        $key = explode(', ', $key);
        $myNewArray = [];
        foreach ($key as $val) {
            $a['$'] = $val;
            array_push($myNewArray, $a);
        }
        $input['keyword'] = $myNewArray;
        //return $input;
        $paper->update($input);

        $paper->author()->detach();
        $paper->teacher()->detach();
        $paper->source()->detach();

        foreach ($request->sources as $key => $value) {
            $v = Source_data::select('id')->where('source_name', '=', $value)->get();
            //return $v;
            $paper->source()->attach($v);
        }
        $x = 0;
        $length = count($request->moreFields);
        //return $request->pos;
        foreach ($request->moreFields as $key => $value) {
            if ($value['userid'] != null) {
                // if ($x === 1) {
                //     $paper->teacher()->attach($value, ['author_type' => 1]);
                // } else if ($x === $length) {
                //     $paper->teacher()->attach($value, ['author_type' => 3]);
                // } else {
                //     $paper->teacher()->attach($value, ['author_type' => 2]);
                // }
                //$paper->user()->attach($value);
                //return $key;
                #print($key);
                $d = $input['pos'][$x];
                $paper->teacher()->attach($value, ['author_type' => $d]);
            }
            $x++;
        }

        $paper->author()->detach();
        $x = 1;

        if (isset($input['fname'][0]) and (!empty($input['fname'][0]))) {
            // $length = count($request->input('fname'));
            foreach ($request->input('fname') as $key => $value) {
                $data['fname'] = $input['fname'][$key];
                $data['lname'] = $input['lname'][$key];
                if (Author::where([['author_fname', '=', $data['fname']], ['author_lname', '=', $data['lname']]])->first() == null) {

                    $author = new Author;
                    $author->author_fname = $data['fname'];
                    $author->author_lname = $data['lname'];
                    $author->save();
                    // if ($x === 1) {
                    //     $paper->author()->attach($author, ['author_type' => 1]);
                    // } else if ($x === $length) {
                    //     $paper->author()->attach($author, ['author_type' => 3]);
                    // } else {
                    //     $paper->author()->attach($author, ['author_type' => 2]);
                    // }
                    $paper->author()->attach($author, ['author_type' => $request->pos2[$key]]);
                } else {
                    $author = Author::where([['author_fname', '=', $data['fname']], ['author_lname', '=', $data['lname']]])->first();
                    $authorid = $author->id;
                    // if ($x === 1) {
                    //     $paper->author()->attach($authorid, ['author_type' => 1]);
                    // } else if ($x === $length) {
                    //     $paper->author()->attach($authorid, ['author_type' => 3]);
                    // } else {
                    //     $paper->author()->attach($authorid, ['author_type' => 2]);
                    // }
                    $paper->author()->attach($authorid, ['author_type' => $request->pos2[$key]]);
                }
                $x++;
            }
        }

        // Log the paper update
        $this->logUpdate('paper', $paper->id, [
            'paper_name' => $paper->paper_name,
            'paper_type' => $paper->paper_type,
            'paper_sourcetitle' => $paper->paper_sourcetitle,
            'paper_yearpub' => $paper->paper_yearpub,
            'paper_volume' => $paper->paper_volume,
            'paper_doi' => $paper->paper_doi
        ]);

        return redirect()->route('papers.index')
            ->with('success', 'papers updated successfully');
        //$paper->author()->detach();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $paper = Paper::find($id);
        
        if ($paper) {
            // Get paper name before deletion for logging
            $paperName = $paper->paper_name;
            
            // Delete the paper
            $paper->delete();
            
            // Log the paper deletion
            $this->logDelete('paper', $id, [
                'paper_name' => $paperName
            ]);
        }
        
        return redirect()->route('papers.index')
            ->with('success', 'paper deleted successfully');
    }

    public function export(Request $request)
    {
        //$export = new ExportPaper($this->getDataForExport());

        return Excel::download(new ExportUser, 'papers.xlsx');
        //return Excel::download(new ExportPaper, 'papers.xlsx');

    }
}
