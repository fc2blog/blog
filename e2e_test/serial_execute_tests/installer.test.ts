import {afterAll, beforeAll, describe, expect, it} from "@jest/globals";
import {Helper} from "../tests/helper";
import * as fs from "fs";

const {execSync} = require("child_process");

describe("crawl admin pages", () => {
  let c: Helper;

  const admin_id = "testadmin@localhost";
  const admin_pass = "testadmin@localhost";
  const blog_id = "testblog1";
  const blog_name = "blog_name";
  const nick_name = "nickname";

  beforeAll(async () => {
    execSync("php " + __dirname + "/../../tests/cli_drop_all_table.php");
    if (fs.existsSync(__dirname + "/../../app/temp/installed.lock")) {
      fs.unlinkSync(__dirname + "/../../app/temp/installed.lock");
    }

    c = new Helper();
    await c.init();
  });

  const start_url = "/admin";
  const installer_url = "/admin/common/install";

  it("redirect to installer page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(c.getBaseUrl() + start_url),
    ]);

    await c.getSS("installer");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
    expect(response.url()).toEqual(c.getBaseUrl() + installer_url);
  });


  it("open installer page", async () => {
    const title_text = await c.page.$eval("h2", elm => elm.textContent);
    expect(/環境チェック/.exec(title_text)).toBeTruthy();
  });

  it("move to form", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#main-contents > form > p > input[type=submit]"),
    ]);

    await c.getSS("install_form");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();
  });

  it("form input error", async () => {

    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#main-contents form input[type=submit]"),
    ]);

    await c.getSS("install_form_input_error");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();

    const error_text = await c.page.$eval("#main-contents > div > p", elm => elm.textContent);
    expect(/入力エラーがあります/.exec(error_text)).toBeTruthy();

  });

  it("form input correctly", async () => {
    await c.getSS("install_form_before_input");

    await c.page.type("#id_form input[name='user[login_id]']", admin_id);
    await c.page.type("#id_form input[name='user[password]']", admin_pass);
    await c.page.type("#id_form input[name='blog[id]']", blog_id);
    await c.page.type("#id_form input[name='blog[name]']", blog_name);
    await c.page.type("#id_form input[name='blog[nickname]']", nick_name);

    await c.getSS("install_form_after_input");

    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#main-contents form input[type=submit]"),
    ]);

    await c.getSS("install_success");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();

    const error_text = await c.page.$eval("body", elm => elm.textContent);
    expect(/入力エラーがあります/.exec(error_text)).not.toBeTruthy();
    expect(/ユーザー登録完了/.exec(error_text)).toBeTruthy();
    expect(/インストール完了/.exec(error_text)).toBeTruthy();
  });

  it("re-open install page. show installed", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(c.getBaseUrl() + installer_url),
    ]);

    await c.getSS("installer_installed");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();

    const body_text = await c.page.$eval("body", elm => elm.textContent);
    expect(/インストール完了/.exec(body_text)).toBeTruthy();
  });

  it("open admin page. and login", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#main-contents > a"),
    ]);

    await c.getSS("admin_login_page");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();

  });

  it("and login", async () => {

    await c.page.type("#id_form input[name='user[login_id]']", admin_id);
    await c.page.type("#id_form input[name='user[password]']", admin_pass);

    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#id_form > tbody > tr:nth-child(3) > td > input[type=submit]"),
    ]);

    await c.getSS("login_success");

    expect(response.status()).toEqual(200);
    expect(await c.isNotAnyNoticeOrWarningsFinishWithEndHtmlTag()).toBeTruthy();

    const body_text = await c.page.$eval("body", elm => elm.textContent);
    expect(/お知らせ/.exec(body_text)).toBeTruthy();
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
