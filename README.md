# Vendor OCD, a Thing for Destiny

Vendor OCD is a PHP script to help determine if you need to buy something from Destiny's various Vendors.

## Configuration

Open `config.php-dist` and fill it out. You'll need a [Bungie API Key](https://www.bungie.net/en/User/API) and credentials for the xbone or psn account linked to your Bungie account. Once you're done, save it as `config.php`.

## Usage

```
php vendor-ocd.php
```

## Example Output

(Once I've got some)

## PHP, seriously?

Yeah, it's a pretty non-terrible way to interact with APIs and it's got cURL support so whatever. I considered using Node and then found that the main Node-based client for the Destiny API _can't hit the API directly_ and _has to bounce off a local proxy server to do literally anything_. That's hilarious, so I noped on out of there and went back to PHP. Because hey, I have to do some crazy stuff to authenticate with Microsoft, but at least I can directly interact with the Bungie API.

## @TODO

[ ] Import what I've done so far
[ ] Figure out how to tell if stuff is actually owned or not
[ ] Iterate over multiple vendors (Holiday, Levante . . uh . . Eververse maybe?)
[ ] idk other stuff
