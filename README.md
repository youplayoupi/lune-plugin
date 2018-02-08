### Intro
Are you looking for a portfolio that you can update via API instead of inputing each transaction manually? But you do not trust any external provider with your API keys? Then this project is for you.

### What
1) Import transactions from CSV files (GDAX, Cryptopia, Binance & Coinbase)
2) Import transactions via API (GDAX, Cryptopia & Binance)
3) Manual transaction import
3) Portfolio page with graphs, coins price, last 24h evolution
4) Ledger page with all imported transaction
5) A page per coin with current market order on GDAX, Cryptopia & Binance

### How

### Installation
1) Download & install Octobercms on your local computer or on your server
2) Login to the administration panel and download the following plugins:
- Users

3) Download lune-theme and lune-plugin from github and copy in theme and plugin folder. Logout from the backend and login again
4) From the administration panel go to "Settings/front-end theme" and activate the lune theme
5) Click on customize lune theme, go to asset and activate "OctoberCMS Front-end Javascript Framework"
6) You can register a new user from the frontend or create (and manually activate) a user from the backend (admin users do not have access to frontend)
7) Login with frontend user on yoururl.com/account (you need to to step 6 before) and go to yoururl.com/ledger, click on the green button "import coins" to import coins info to DB

### Technical info
This project is based on Octobercms a Laravel based CMS.
Theme is based on "Octaskin" template from laratify

### Thanks to
- Octobercms
- laratify (for the theme template)
- u/waveon3 for the overall theme design

### Help
Feel free to share your ideas & improvement on reddit:
We would really appreciate help with:
- the template
- code review

Any donation is more than welcomed:
- Doge DNQ2FNybQmU28bejcAKuMJTrFbS41epTcW
- Eth 0xdbCB7602fBe55C53563400C4827e9f6e6e892690
- LTC LdJ5JbSHQUgbLGj6xWzxRU1fUd5EfXunHx
- XMR 45kNrpPZDnjDdnYUj1HFy9HVDVcobL4CwJpwWbhWCNxJbYVBPwFDU4YfFJRtxCzfXkeid5VYFqrC3JSb3rsm4kJPECBNzj4