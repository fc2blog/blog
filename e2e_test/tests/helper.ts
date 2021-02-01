import puppeteer = require("puppeteer");
import dotenv = require("dotenv");
import * as process from "process";
import { Browser, Page, Response } from "puppeteer";

dotenv.config();

export class Helper {
  page: Page;
  browser: Browser;
  requestHttpHeaders: Record<string, string>;

  constructor() {}

  getBaseUrl(): string {
    return process.env.BASE_URL || "https://localhost:8480/";
  }

  async init(): Promise<void> {
    this.requestHttpHeaders = {
      'Accept-Language': 'ja,en-US;q=0.9,en;q=0.8',
      'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36'
    };
    this.browser = await puppeteer.launch({
      args: ['--lang=ja'],
      ignoreHTTPSErrors: true,
      headless: !(process.env.NO_HEAD_LESS === "1") // 動作を確認するなら NO_HEAD_LESS=1 npm run test
    });
    this.page = await this.browser.newPage();
    await this.page.setExtraHTTPHeaders(this.requestHttpHeaders);
    await this.page.authenticate({
      username: process.env.BASIC_ID || "",
      password: process.env.BASIC_PASS || "",
    });
    // memo 環境によってはスクショ作成のためにViewportが大きくしすぎると一部テストが正しく動作しない?
    await this.page.setViewport({
      width: 1000,
      height: parseInt(process.env.VIEW_PORT_HEIGHT) || 1000,
    });
  }

  async setSpUserAgent(): Promise<void>{
    let spRequestHttpHeaders = this.requestHttpHeaders;
    spRequestHttpHeaders['User-Agent'] = 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Mobile Safari/537.36';
    await this.page.setExtraHTTPHeaders(spRequestHttpHeaders);
    await this.page.setViewport({
      width: 400,
      height: parseInt(process.env.VIEW_PORT_HEIGHT) || 811,
    });
  }

  async getTitle(): Promise<string> {
    return await this.page.title();
  }

  waitDom(): Promise<Response> {
    return this.page.waitForNavigation({ waitUntil: "domcontentloaded" });
  }

  waitLoad(): Promise<Response> {
    return this.page.waitForNavigation({ waitUntil: ["load", "networkidle2"] });
  }

  async getSS(name): Promise<void> {
    await this.page.screenshot({ path: "ss/" + name + ".png" });
  }

  async click(selector): Promise<void> {
    return await this.page.click(selector);
  }

  async clickAndWaitResponse(selector): Promise<Response|void> {
    let some = await Promise.all([this.waitLoad(), this.page.click(selector)]);

    return some[0];
  }

  async isExists(selector): Promise<boolean> {
    const elms = await this.page.$$(selector);
    return elms.length > 0;
  }

  async setCheckBox(selector): Promise<void> {
    await Promise.all([
      this.page.$eval(selector, (elem) => (elem as HTMLElement).click()),
      this.page.waitForSelector(selector),
    ]);
  }

  // 画面にPHPのNoticeやWarningエラーがでておらず、</html>まで出力されている
  async isNotAnyNoticeOrWarningsFinishWithEndHtmlTag(): Promise<boolean> {
    const whole_text = await this.page.$eval("html", (elm) => elm.textContent);
    const whole_html = await this.page.evaluate(
      () => document.documentElement.outerHTML
    );
    return (
      /(Notice: |Warning: )/.exec(whole_text) === null &&
      /<\/html>/.exec(whole_html) !== null
    );
  }
}
