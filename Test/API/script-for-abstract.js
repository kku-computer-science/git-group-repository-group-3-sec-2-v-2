/********************************************************************
 * 1) ตรวจสอบสถานะการตอบสนอง (HTTP 200)
 ********************************************************************/
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

/********************************************************************
 * 2) ตรวจสอบว่ามี abstracts-retrieval-response
 ********************************************************************/
pm.test("Response has 'abstracts-retrieval-response'", function () {
    let jsonData = pm.response.json();
    pm.expect(jsonData, "Must contain 'abstracts-retrieval-response'")
      .to.have.property("abstracts-retrieval-response");
});

/********************************************************************
 * 3) ตรวจสอบ abstracts-retrieval-response.item 
 *    (โค้ด Controller ใช้ .json('abstracts-retrieval-response.item'))
 ********************************************************************/
pm.test("'abstracts-retrieval-response' has 'item'", function () {
    let absResp = pm.response.json()["abstracts-retrieval-response"];
    pm.expect(absResp, "abstracts-retrieval-response should have 'item'")
      .to.have.property("item");
});

/********************************************************************
 * 4) ตรวจโครงสร้างหลักใน 'item': bibrecord, head, xocs:meta, etc.
 ********************************************************************/
pm.test("Check structure in 'abstracts-retrieval-response.item'", function() {
    let itemData = pm.response.json()["abstracts-retrieval-response"].item;

    // 4.1 ตรวจว่า itemData มี 'bibrecord' และ 'bibrecord.head'
    pm.expect(itemData, "item should have 'bibrecord'").to.have.property("bibrecord");
    pm.expect(itemData["bibrecord"], "bibrecord should have 'head'")
      .to.have.property("head");

    // 4.2 ตรวจ citation-title (Controller ใช้แทน paper_name ได้)
    let head = itemData["bibrecord"]["head"];
    pm.expect(head, "head should have 'citation-title'")
      .to.have.property("citation-title");

// 4.3 ตรวจ abstracts จาก bibrecord.head หรือ coredata.dc:description
    pm.test("Check if abstracts exist in bibrecord.head or coredata", function() {
        // ดึง abstracts จาก bibrecord.head (ถ้ามี)
        let hasHeadAbstracts = head.hasOwnProperty("abstracts");
        
        // ดึง dc:description จาก coredata (ถ้ามี)
        let coredata = pm.response.json()["abstracts-retrieval-response"].coredata;
        let hasCoredataAbstracts = coredata && coredata.hasOwnProperty("dc:description");
        
        // ต้องมี abstract อย่างน้อยที่หนึ่งแหล่ง
        pm.expect(hasHeadAbstracts || hasCoredataAbstracts, 
            "Must have abstracts either in bibrecord.head or coredata.dc:description")
            .to.be.true;
            
        // ถ้ามีใน head ตรวจสอบ type
        if (hasHeadAbstracts) {
            let absVal = head["abstracts"];
            pm.expect(absVal).to.satisfy(v => {
                return Array.isArray(v) || (typeof v === "object") || (typeof v === "string");
            });
        }
        
        // ถ้ามีใน coredata ตรวจสอบว่าเป็น string
        if (hasCoredataAbstracts) {
            pm.expect(coredata["dc:description"]).to.be.a("string");
        }
    });

    // 4.4 ตรวจ "xocs:meta" → "xocs:funding-list" → "xocs:funding-text"
    pm.expect(itemData, "item should have 'xocs:meta'").to.have.property("xocs:meta");
    let xocsMeta = itemData["xocs:meta"];
    if (xocsMeta.hasOwnProperty("xocs:funding-list")) {
        let fundingList = xocsMeta["xocs:funding-list"];
        // funding-text เป็น optional
        if (fundingList.hasOwnProperty("xocs:funding-text")) {
            pm.test("'xocs:funding-text' can be string/array/object", function () {
                let ftext = fundingList["xocs:funding-text"];
                pm.expect(ftext).to.satisfy(x => {
                    return typeof x === "string" || Array.isArray(x) || typeof x === "object";
                });
            });
        }
    }
});

/********************************************************************
 * 5) ตรวจ authors (2 แห่งใน JSON):
 *    1) bibrecord.head.author-group.author 
 *    2) abstracts-retrieval-response.authors.author (ถ้ามี)
 ********************************************************************/
pm.test("Check authors in 'bibrecord.head.author-group.author' or 'abstracts-retrieval-response.authors'", function() {
    let absResp = pm.response.json()["abstracts-retrieval-response"];
    let itemData = absResp.item;
    let head     = itemData?.bibrecord?.head;

    // 5.1 author-group (ถ้ามี)
    if (head && head.hasOwnProperty("author-group")) {
        let ag = head["author-group"];
        pm.test("'author-group' found in head", function() {
            // อาจเป็น object เดียว หรือ array
            if (Array.isArray(ag)) {
                // loop ทุก group
                ag.forEach((grp, i) => {
                    pm.expect(grp, `author-group[${i}] should have 'author'`)
                      .to.have.property("author");
                });
            } else {
                // ถ้าเป็น object
                pm.expect(ag, "author-group object").to.have.property("author");
            }
        });
    }

    // 5.2 abstracts-retrieval-response.authors
    if (absResp.hasOwnProperty("authors")) {
        pm.test("Check 'abstracts-retrieval-response.authors.author'", function() {
            let authorsObj = absResp.authors;
            pm.expect(authorsObj).to.have.property("author");
        });
    }
});

/********************************************************************
 * 6) ตรวจข้อมูลเชิงลึกของ author (ชื่อ, นามสกุล, affiliation)
 ********************************************************************/
pm.test("Check author details (ce:given-name, ce:surname, affiliation)", function() {
    let absResp = pm.response.json()["abstracts-retrieval-response"];
    let itemData = absResp.item;
    let head     = itemData?.bibrecord?.head;

    // ฟังก์ชันสำหรับตรวจลึกระดับ author
    function checkAuthorArray(authorList) {
        authorList.forEach((auth, idx) => {
            // หา givenName/surname (ce:given-name / ce:surname) หรือใน preferred-name
            let givenName = auth["ce:given-name"] ?? auth["preferred-name"]?.["ce:given-name"];
            let surName   = auth["ce:surname"]    ?? auth["preferred-name"]?.["ce:surname"];

            pm.expect(givenName, `author[${idx}] => given-name`).to.not.be.undefined;
            pm.expect(surName,   `author[${idx}] => surname`).to.not.be.undefined;

            // affiliation (Controller จะ loop affiliation เช็คว่าเป็น KKU หรือไม่)
            // JSON ล่าสุด: affiliation เป็น object เช่น {"@id": "60017165", ...}  
            if (auth.hasOwnProperty("affiliation")) {
                pm.test(`author[${idx}] => affiliation`, () => {
                    let aff = auth["affiliation"];
                    // บางทีเป็น object เดียว, บางที array
                    pm.expect(aff, "affiliation should be array or object").to.satisfy(a => {
                        return Array.isArray(a) || typeof a === "object";
                    });

                    // ถ้าเป็น array
                    if (Array.isArray(aff)) {
                        aff.forEach((affObj, j) => {
                            // ตรวจสอบคีย์บางตัว ถ้ามี
                            pm.expect(affObj, `affiliation[${j}] has '@id'`).to.have.property("@id");
                        });
                    } else {
                        // ถ้าเป็น object
                        pm.expect(aff, "affiliation object should have '@id' or 'city' or 'organization'").to.satisfy(o => {
                            return o.hasOwnProperty("@id") || o.hasOwnProperty("organization") || o.hasOwnProperty("affilname");
                        });
                    }
                });
            }
        });
    }

    // 6.1 ดึง author จาก author-group
    if (head && head.hasOwnProperty("author-group")) {
        let ag = head["author-group"];
        if (Array.isArray(ag)) {
            ag.forEach((grp) => {
                if (grp.hasOwnProperty("author")) {
                    let a = grp.author;
                    if (!Array.isArray(a)) {
                        a = [a];
                    }
                    checkAuthorArray(a);
                }
            });
        } else {
            // author-group เป็น object
            if (ag.hasOwnProperty("author")) {
                let a = ag.author;
                if (!Array.isArray(a)) {
                    a = [a];
                }
                checkAuthorArray(a);
            }
        }
    }

    // 6.2 ดึง author จาก abstracts-retrieval-response.authors
    if (absResp.hasOwnProperty("authors")) {
        let aData = absResp.authors;
        if (aData.hasOwnProperty("author")) {
            let arr = aData.author;
            if (!Array.isArray(arr)) {
                arr = [arr];
            }
            checkAuthorArray(arr);
        }
    }
});

/********************************************************************
 * 7) ตรวจ affiliation ที่อยู่ระดับ abstracts-retrieval-response.affiliation
 *    (อยู่นอก itemData, เช่น "affiliation" : {...})
 ********************************************************************/
pm.test("Check top-level 'affiliation' in 'abstracts-retrieval-response'", function() {
    let absResp = pm.response.json()["abstracts-retrieval-response"];
    if (absResp.hasOwnProperty("affiliation")) {
        let affObj = absResp.affiliation;
        pm.expect(affObj, "affiliation object should have @id").to.have.property("@id");
        // ดูว่ามี affilname หรือไม่
        pm.test("affiliation has 'affilname'", () => {
            pm.expect(affObj, "should have affilname or organization").to.satisfy(o => {
                return o.hasOwnProperty("affilname") || o.hasOwnProperty("organization");
            });
        });
    }
});

/********************************************************************
 * 8) ตรวจ coredata (dc:description, prism:coverDate, prism:doi, etc.)
 ********************************************************************/
pm.test("Check 'coredata' structure", function() {
    let absResp = pm.response.json()["abstracts-retrieval-response"];
    let coredata = absResp.coredata;

    pm.expect(absResp, "abstracts-retrieval-response should have 'coredata'")
      .to.have.property("coredata");

    // ฟิลด์บางตัวใน coredata ที่ Controller ใช้อาจเป็น fallback เช่น 'dc:description'
    pm.expect(coredata, "coredata should have 'dc:description'")
      .to.have.property("dc:description");
    
    pm.expect(coredata, "coredata should have 'prism:coverDate'")
      .to.have.property("prism:coverDate");

    pm.expect(coredata, "coredata should have 'prism:aggregationType'")
      .to.have.property("prism:aggregationType");

    pm.expect(coredata, "coredata should have 'dc:title'")
      .to.have.property("dc:title");

    // prism:doi (optional => ถ้าไม่มีให้ตัว Controller ใส่ null)
    // ถ้ามีให้ตรวจสอบว่าเป็น string
    if (coredata.hasOwnProperty("prism:doi")) {
        pm.test("Check 'prism:doi' is string", () => {
            pm.expect(coredata["prism:doi"]).to.be.a("string");
        });
    }
});

/********************************************************************
 * 9) สรุป: ถ้าทดสอบผ่านทั้งหมด แสดงว่าโครงสร้าง JSON ตรงตาม 
 *    ที่ Controller ใช้งานได้สมบูรณ์
 ********************************************************************/
