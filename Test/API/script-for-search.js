/***************************************************************
 * ตรวจสอบสถานะการตอบสนอง (HTTP Status)
 ***************************************************************/
pm.test("Status code is 200", function () {
    pm.response.to.have.status(200);
});

/***************************************************************
 * ตรวจสอบโครงสร้าง JSON เบื้องต้น
 ***************************************************************/
pm.test("Response contains 'search-results' with 'entry' array", function () {
    let jsonData = pm.response.json();

    pm.expect(jsonData, "Response should have search-results").to.have.property("search-results");
    pm.expect(jsonData["search-results"], "search-results should have entry").to.have.property("entry");
    pm.expect(jsonData["search-results"]["entry"], "entry should be an array").to.be.an("array");

    // ตรวจสอบว่ามีผลลัพธ์อย่างน้อย 1 รายการ
    pm.expect(jsonData["search-results"]["entry"].length, "entry should not be empty").to.be.above(0);
});

/***************************************************************
 * ตรวจสอบฟิลด์/ข้อมูลสำคัญที่ Controller ใช้ในแต่ละ entry
 ***************************************************************/
pm.test("Check each entry for required fields used in ScopuscallController", function () {
    let entries = pm.response.json()["search-results"]["entry"];

    entries.forEach((item, index) => {

        // ----------------------
        // 1) ฟิลด์หลักที่ใช้
        // ----------------------
        pm.expect(item, `Entry #${index} must have 'dc:title'`).to.have.property("dc:title");
        pm.expect(item, `Entry #${index} must have 'dc:identifier'`).to.have.property("dc:identifier");
        pm.expect(item, `Entry #${index} must have 'prism:aggregationType'`).to.have.property("prism:aggregationType");

        // ฟิลด์สำคัญที่เอาไปใช้ set เป็น paper->paper_subtype / paper->paper_sourcetitle
        pm.expect(item, `Entry #${index} must have 'subtype'`).to.have.property("subtype");
        pm.expect(item, `Entry #${index} must have 'subtypeDescription'`).to.have.property("subtypeDescription");

        // ฟิลด์ที่ใช้ดึงปี (paper_yearpub)
        pm.expect(item, `Entry #${index} must have 'prism:coverDate'`).to.have.property("prism:coverDate");

        // ฟิลด์ที่เกี่ยวกับการอ้างอิง (paper_citation)
        pm.expect(item, `Entry #${index} must have 'citedby-count'`).to.have.property("citedby-count");

        // ฟิลด์ที่ใช้เช็คชื่อวารสาร/แหล่งตีพิมพ์
        pm.expect(item, `Entry #${index} must have 'prism:publicationName'`).to.have.property("prism:publicationName");

        // ลิงก์ต่าง ๆ (Controller จะวนหา @ref = 'scopus')
        pm.expect(item, `Entry #${index} must have 'link'`).to.have.property("link");
        pm.expect(item.link, `Entry #${index} link should be array`).to.be.an("array");

        // ----------------------
        // 2) ฟิลด์ที่อาจไม่มี (Optional)
        // ----------------------
        // author-keywords
        // ถ้ามี ก็จะเป็น object/array แล้วแต่กรณี ใน Controller ใส่ json_encode
        // ถ้าไม่มี ก็จะเป็น undefined => Controller จะเซ็ตเป็น null
        // เราจะตรวจสอบแบบมีเงื่อนไข
        if (item.hasOwnProperty("author-keywords")) {
            pm.test(`(Optional) Entry #${index} has author-keywords`, function() {
                pm.expect(item["author-keywords"]).to.be.an("object").or.an("array");
            });
        }

        // prism:doi
        // ถ้าข้อมูลมี DOI ก็ต้องมี property นี้ ถ้าไม่มี เป็น undefined => Controller จะเซ็ต null
        if (item.hasOwnProperty("prism:doi")) {
            pm.test(`(Optional) Entry #${index} has prism:doi`, function () {
                pm.expect(item["prism:doi"]).to.be.a("string");
            });
        }

        // prism:pageRange
        if (item.hasOwnProperty("prism:pageRange")) {
            pm.test(`(Optional) Entry #${index} has pageRange`, function () {
                pm.expect(item["prism:pageRange"]).to.be.a("string");
            });
        }

        // prism:volume
        if (item.hasOwnProperty("prism:volume")) {
            pm.test(`(Optional) Entry #${index} has volume`, function () {
                pm.expect(item["prism:volume"]).to.be.a("string");
            });
        }

        // prism:issueIdentifier
        if (item.hasOwnProperty("prism:issueIdentifier")) {
            pm.test(`(Optional) Entry #${index} has issueIdentifier`, function () {
                pm.expect(item["prism:issueIdentifier"]).to.be.a("string");
            });
        }
    });
});

/***************************************************************
 * ตรวจสอบค่า totalResults (optional)
 ***************************************************************/
pm.test("Check opensearch:totalResults is present", function () {
    let searchRes = pm.response.json()["search-results"];
    pm.expect(searchRes, "search-results should have 'opensearch:totalResults'").to.have.property("opensearch:totalResults");
    pm.expect(parseInt(searchRes["opensearch:totalResults"]), "totalResults should be >= 0").to.be.at.least(0);
});

/***************************************************************
 * (Option) ตรวจสอบลิงก์ next/last ด้วย
 ***************************************************************/
pm.test("Check 'link' inside search-results", function () {
    let links = pm.response.json()["search-results"].link;
    // ควรเป็น array ที่มี self, first, next, last
    pm.expect(links).to.be.an("array");
    let hrefList = links.map(l => l["@href"]);
    pm.expect(hrefList.length, "there should be at least 1 link").to.be.at.least(1);
});


