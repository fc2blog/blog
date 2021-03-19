import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl admin pages", () => {
  let c: Helper;

  const admin_id = "testadmin";
  const admin_pass = "testadmin";

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "/admin/";

  it("login page", async () => {
    let response;
    [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(c.getBaseUrl() + start_url),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    await c.page.$eval(
      "#id_form input[name='user[login_id]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#id_form input[name='user[login_id]']", admin_id);
    await c.page.$eval(
      "#id_form input[name='user[password]']",
      (elm: HTMLInputElement) => (elm.value = "")
    );
    await c.page.type("#id_form input[name='user[password]']", admin_pass);

    [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click("#id_form input[type=submit]"),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + "/admin/common/notice");
  });

  it("open ファイルアップロード", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click(
        "#left-nav > div:nth-child(1) > ul > li:nth-child(6) > a[href='/admin/files/upload']"
      ),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + "/admin/files/upload");
  });

  let upload_file_op = async () => {
    const input_file = await c.page.$('input[type=file][name="file[file]"]');
    const test_image_path = __dirname+'/test.png';
    await input_file.uploadFile(test_image_path);

    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click('#sys-file-form > p > input[type=submit]'),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + "/admin/files/upload");
  }

  it("upload file 1", upload_file_op);
  it("upload file 2", upload_file_op);

  const count_file_num = async(c)=>{
    const whole_text = await c.page.$eval("html", (elm) => elm.textContent);
    const match = whole_text.match(/ファイル検索\[該当[\s]*([0-9]+)件]/);
    if(!match){
      console.log("一件もない");
      return null;
    }else{
      return Number.parseInt(match[1]);
    }
  }

  let before_file_num ;
  it("count before file num", async () => {
    before_file_num = await count_file_num(c);
  });

  it("delete a file", async () => {
    c.page.on('dialog', async dialog => {
      await dialog.accept();
    });

    let [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#sys-ajax-files-index > table > tbody > tr > td:nth-child(6) > form > button")
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
  });

  it("count result file num", async () => {
    expect(await count_file_num(c)).toEqual(before_file_num-1);
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
