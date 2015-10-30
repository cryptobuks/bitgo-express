<img src="https://raw.githubusercontent.com/bitgo/bitgo-express/master/misc/img/logo.png" alt="BitGo Express" width="300">

BitGo Express makes it easy to build multi-signature Bitcoin applications in any language. BitGo Express provides a local REST API interface which handles all client-side operations involving your private keys. This ensures your keys never leave your network, and are not seen by BitGo. 

BitGo Express can also proxy the standard BitGo REST APIs, providing a unified interface to BitGo through a single REST API.

# Run

Basic

`./bin/bitgo-express --debug --port 3080 --env test --bind localhost`

Advanced

`./bin/bitgo-express [-h] [-v] [-p PORT] [-b BIND] [-e ENV] [-d] [-l LOGFILEPATH] [-k KEYPATH] [-c CRTPATH]`

*Make* **ALL** *BitGo REST API calls to the machine on which bitgo-express is running*

# Installation

`npm install`

# Documentation

https://www.bitgo.com/api/#bitgo-express-rest-api

# Quick Start

[Click here] (https://medium.com/@masonic_tweets/getting-started-with-bitgo-express-a097589c1e9c) to view our quick start guide. 




