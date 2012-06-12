### libWebThumbnail

A library for the Content Management Systems [WebsiteBaker] [1] and [LEPTON CMS] [2] to access the API of WebThumbnail.org to create screenshots of websites. 

Uses the [cepa PHP client] [4], this library can additional save the screenshots local and update the saved files from time to time, using a cronjob.

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1]
* _or_ using [LEPTON CMS] [2]

#### Installation

* download the actual [libWebThumbnail_x.xx.zip] [3] installation archive
* in the CMS backend select the file from "Add-ons" -> "Modules" -> "Install module"
* libWebThumbNail will also install the droplet `webthumbnail`

#### First Steps

* edit the page where you want to add a Screenshot of a website
* insert the droplet `webthumbnail` at the right place

        [[webthumbnail?url=http://phpmanufaktur.de]]

* the _parameter_ `url` is obligatory, as _value_ type in the address of the website you want to make a screenshot from
* save the page and look at the result, your first WebThumbnail is ready! 
* at the next call **libWebThumbnail** will use the local saved thumbnail instead of accessing the WebThumbnail service.
  
#### Parameters for the droplet

There are some _parameters_ you can use with the droplet:

* `url` - the URL address
* `browser` - the Webbrowser, WebThumbnail should use to make a thumbnail. Allowed _values_ are `chrome` (default), `firefox` and `opera`
* `format` - the image format, allowed _values_ are `png` (default), `jpg` and `gif`
* `width` - the width of the thumbnail in pixel, default is _196_
* `height` - the height of the thumbnail in pixel, default is _196_
* `alt` - the content of the alternate tag (used for the image)
* `title` - the content of the title tag (used for the image and the link)
* `class_image` - you can specify an additional _class_ for the image tag
* `class_link` - you can specify an additional _class_ for the link tag
* `target` - specify the _target_ of the link, default is `_blank`
* `do_link` - allowed _values_ are `true` (default) and `false`, which switch the link of
* `html_strict` - force _HTML STRICT_ (suppress `target`)

Example:

        [[webthumbnail?url=http://phpmanufaktur.de&width=300&height=500&alt=phpManufaktur&format=jpg&browser=firefox]]
        
This command will create a thumbnail of the phpManufaktur, 300 pixel width, 500 pixel height in JPEG format. WebThumbnail will use Firefox for the screenshot.

#### Auto update of the thumbnails

If you want to automatically update your thumbnails please use a _cronjob_ and call:

        http://yourdomain.tld/modules/lib_webthumbnail/cronjob.php
        
`cronjob.php` will update thumbsnails, which are older than a week. To avoid running out of time, `cronjob.php` process only one thumbnail at each call.

Please visit [WebThumbnail] [5] to get more informations about **WebThumbnail** and join the [Addons Support Group] [6] for technical support.

[1]: http://websitebaker2.org "WebsiteBaker Content Management System"
[2]: http://lepton-cms.org "LEPTON CMS"
[3]: https://github.com/phpManufaktur/libWebThumbnail/downloads
[4]: https://github.com/cepa/webthumbnail
[5]: http://webthumbnail.org
[6]: https://phpmanufaktur.de/support
