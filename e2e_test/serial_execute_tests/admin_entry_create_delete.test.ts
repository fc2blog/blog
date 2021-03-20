import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "../tests/helper";

describe("admin create entry", () => {
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

  it("open 新しく記事を書く", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click(
        "#left-nav > div:nth-child(1) > ul > li:nth-child(3) > a[href='/admin/entries/create']"
      ),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(
      c.getBaseUrl() + "/admin/entries/create"
    );
  });

  it("post entry and fail", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click(
        "#sys-entry-form-submit"
      ),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + "/admin/entries/create");
  });

  const find_text = async(c, str, selector)=>{
    const whole_text = await c.page.$eval(selector, (elm) => elm.textContent);
    const match = whole_text.match(new RegExp(str));
    return !!match;
  }

  it("check blank title post error text", async () => {
    let [response] = await Promise.all([
      c.waitLoad(),
      await c.page.click(
        "#sys-entry-form-submit"
      ),
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + "/admin/entries/create");
    expect(await find_text(c, "必ず入力してください","#sys-entry-form > table > tbody > tr > td > p")).toEqual(true);
  });


  it("post and success", async () => {
    await c.page.type(
      '#sys-entry-form > table > tbody > tr > td > input[type=text]',
      "blog post title"
    );

    let [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#sys-entry-form-submit")
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(await find_text(c, "記事を作成しました","#main-contents > div.flash-message.flash-message-info > p")).toEqual(true);
  });

  it("delete test", async () => {
    c.page.on('dialog', async dialog => {
      await dialog.accept();
    });

    let [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#sys-list-form > table > tbody > tr:nth-child(1) > td:nth-child(8) > a")
    ]);

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(await find_text(c, "記事を削除しました","#main-contents > div.flash-message.flash-message-info > p")).toEqual(true);
  });

  //
  afterAll(async () => {
    await c.browser.close();
  });
});
