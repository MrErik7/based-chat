# Based Chat

A web-based chat application with user accounts and basic message encryption, built as a personal project to practice full-stack web development. It covers registration, login, and messaging, backed by a database.

## Features

- User registration and login
- Sending and reading chat messages
- Basic encryption of messages
- Persistent user and message data in a database

## Built with

- PHP (backend)
- JavaScript (frontend logic)
- HTML and CSS (interface)
- An SQL database (`baseddb`)

## Pages

- `register.html`: create an account
- `login.html`: sign in
- `chat.html`: the chat interface

## Running it

1. Place the project in the web root of a PHP-capable server such as XAMPP or a LAMP stack.
2. Create the database and tables from the files in `baseddb`, and update the database connection settings in the PHP files if needed.
3. Open the site in your browser, register an account, and start chatting.

## Known limitations

This is a learning project and is not hardened for production use. In particular, the current version does not fully protect against SQL injection; moving the database queries to prepared statements would be the next step before any real deployment.
