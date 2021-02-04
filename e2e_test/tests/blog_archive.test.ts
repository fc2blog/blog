import { afterAll, beforeAll, describe, expect, it } from "@jest/globals";
import { Helper } from "./helper";

describe("crawl some blog", () => {
  let c: Helper;

  beforeAll(async () => {
    c = new Helper();
    await c.init();
  });

  const start_url = "http://localhost:8080/testblog2/";

  it("open blog top", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url),
    ]);

    await c.getSS("blog_top");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url);
    expect(await c.page.title()).toEqual("testblog2");
  });

  it("open archive page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#headermenu > p > a"),
    ]);

    await c.getSS("blog_archive.png");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "archives.html");

    expect(await c.page.title()).toEqual("記事一覧 - testblog2");

    const h2_text = await c.page.$eval(
      "#titlelist > h2",
      (elm) => elm.textContent
    );
    expect(h2_text.match(/インデックス/)).not.toBeNull();

    const entry_list = await c.page.$$("#titlelist > ul > li");
    expect(entry_list.length).toEqual(3);

    const link_text = await c.page.$eval(
      "#titlelist > ul > li:nth-child(1)",
      (elm) => elm.textContent
    );
    expect(
      link_text.match(/[0-9]{4}\/[0-9]{2}\/[0-9]{2}：3rd：未分類/)
    ).not.toBeNull();
  });

  it("open some page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#titlelist > ul > li:nth-child(1) > a:nth-child(1)"),
    ]);

    await c.getSS("blog_archive.png");
    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "blog-entry-3.html");

    expect(await c.page.title()).toEqual("3rd - testblog2");
  });

  it("open archive page", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.goto(start_url + "archives.html"),
    ]);

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(start_url + "archives.html");
  });

  it("open some category", async () => {
    const [response] = await Promise.all([
      c.waitLoad(),
      c.page.click("#titlelist > ul > li:nth-child(1) > a:nth-child(2)"),
    ]);

    await c.getSS("blog_category");

    expect(response.status()).toEqual(200);
    expect(response.url()).toEqual(
      start_url + "index.php?mode=entries&process=category&cat=1"
    );

    expect(await c.page.title()).toEqual("未分類 - testblog2");
  });

  afterAll(async () => {
    await c.browser.close();
  });
});
