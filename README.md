# Vendor OCD, a Thing for Destiny

Vendor OCD is a PHP script to help determine if you need to buy something from Destiny's various Vendors.

## Configuration

Open `config.php-dist` and fill it out. You'll need a [Bungie API Key](https://www.bungie.net/en/User/API) and credentials for the xbone or psn account linked to your Bungie account. Once you're done, save it as `config.php`.

## Usage

```
php vendor-ocd.php
```

## Example Output

```
$ php vendor-ocd.php
===========================
Eva Levante - Outfitter
Next Refresh in 6 hours 32 minutes 25 seconds
===========================
Emblems
 - Born of Fire

Shaders
 - Got 'em all, come back later'

===========================
Amanda Holiday - Shipwright
Next Refresh in 1 day 6 hours 32 minutes 24 seconds
===========================
Ship Blueprints
 - Kestrel Class EX
 - Kestrel Class AX0

Vehicles
 - S-22 Cavalier
 - S-21 Cavalier
 - S-20 Nomad
 - S-22 Nomad
 - S-22 Seeker
 - S-21 Seeker
```

## PHP, seriously?

Yeah, it's a pretty non-terrible way to interact with APIs and it's got cURL support so whatever. I considered using Node and then found that the main Node-based client for the Destiny API _can't hit the API directly_ and _has to bounce off a local proxy server to do literally anything_. That's hilarious, so I noped on out of there and went back to PHP. Because hey, I have to do some crazy stuff to authenticate with Microsoft, but at least I can directly interact with the Bungie API.

## @TODO

- [x] ~~Import what I've done so far~~
- [x] ~~Figure out how to tell if stuff is actually owned or not~~ (ProTip: You can only see it in Kiosks, and it's in a "unlockStatuses" flag hanging off items)
- [x] ~~Iterate over multiple vendors (Holiday, Levante . . uh . . Eververse maybe?)~~
- [x] ~~A crappy web version~~
- [ ] A slightly less crappy web version (stylesheets, render icons from bungo, etc)
- [ ] Include prices?
- [ ] Crappy low-fi caching . . maybe?
- [ ] Check more vendors? Eververse? Variks?
- [ ] Add some extra flags for manually flushing login state, etc
- [ ] idk other stuff
