import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl notfound page", () => {
  let c: Helper;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "http://localhost:8080/testblog2/?no=99999999999";

  it("open nof found page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("notfound");

    expect(response.status()).toEqual(200); // FIXME TODO 404であるべき
    expect(response.url()).toEqual(start_url);
    expect(await c.page.title()).toEqual("testblog2");

    const body_text = await c.page.$eval("body", elm=>elm.textContent);
    expect(body_text.match(/404 Not Found お探しのページは存在しません/));
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
