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
Next Refresh in 10 hours 32 minutes 10 seconds
===========================
Emblems
 [ ] The Rising Night
 [ ] Cassoid
 [ ] Abraxas
 [ ] Crypto Shift
 [ ] Mammoth II
 [ ] Mammoth
 [ ] Häkke
 [ ] Suros
 [ ] Crux/Lomar
 [ ] Tex Mechanica
 [ ] Cyclops Mind
 [$] Sigil of Seven
 [ ] Blessing of the Gifted
 [ ] Blessing of the Sentinel
 [ ] Blessing of the Knight
 [ ] Blessing of the Ancients
 [ ] Blessing of IV
 [ ] Blessing of Worlds

Shaders
 [ ] Aurora Blur
 [ ] Thunderdevil
 [ ] Polar Oak
 [ ] Broadsword
 [ ] Classified
 [ ] 18327496-64703388


===========================
Amanda Holiday - Shipwright
Next Refresh in 2 days 10 hours 32 minutes 6 seconds
===========================
Ship Blueprints
 [$] Regulus Class 66c
 [$] Kestrel Class CX0
 [$] LRv2 Javelin
 [$] Phaeton Class v2.1
 [$] Kestrel Class AX
 [$] Phaeton Class v1

Vehicles
 [$] S-20 Cavalier
 [$] S-21 Cavalier
 [$] S-22 Nomad
 [$] S-20 Nomad
 [$] S-21 Seeker
 [$] S-20 Seeker
```

## PHP, seriously?

Yeah, it's a pretty non-terrible way to interact with APIs and it's got cURL support so whatever. I considered using Node and then found that the main Node-based client for the Destiny API _can't hit the API directly_ and _has to bounce off a local proxy server to do literally anything_. That's hilarious, so I noped on out of there and went back to PHP. Because hey, I have to do some crazy stuff to authenticate with Microsoft, but at least I can directly interact with the Bungie API.

## @TODO

- [x] ~~Import what I've done so far~~
- [x] ~~Figure out how to tell if stuff is actually owned or not~~ (ProTip: You can only see it in Kiosks, and it's in a "unlockStatuses" flag hanging off items)
- [x] ~~Iterate over multiple vendors (Holiday, Levante . . uh . . Eververse maybe?)~~
- [ ] Check more vendors? Eververse? Variks?
- [ ] Add some extra flags for manually flushing login state, etc
- [ ] idk other stuff
