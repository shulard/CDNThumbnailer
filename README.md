# CDNThumbnailer

CDNThumbnailer is a php tool for dynamically image resizing. It is distributed with a cache and a CDN controller to allow quick files access. It include compatibility with GD and ImageMagick php extension.

This tool is written in **PHP 5.2** to maximize compatibility (but in a **PHP 5.3** style).

## Documentation, help

This tool can be used as a library inside a project for image resizing. It can also be included as a HTTP Proxy for image resizing.
It is compatible with external images and allow dynamic resizing and serving of images.

If you need to use it as a proxy, there are some initialization to perform:

 - Copy [SAMPLE.htaccess](SAMPLE.htaccess) to *.htaccess* (this new file is ignored by .gitignore), replace **%{DOCUMENT_ROOT}/cache** by the absolute path to the cache folder and **RewriteBase** by the valid one in your project.
 - Copy [config/SAMPLE.config.inc.php](config/SAMPLE.config.inc.php) to *config/config.inc.php* and update the **CACHE_FOLDER** constant if you use a custom layout (let it if not).
 
To call the CDN, you need to use URLs like that:

 - **http://[yourhost]/28x28/path/to/your/image.png** : This URL request the file *cache/28x28/path/to/your/image.png* to be served. If exists, it is returned, else the file *cache/original/path/to/your/image.png* will be used as master for resize (if this file does not exists, HTTP Status code is returned as 404).
 - If you use external images as content source, you need to defined the scheme in the URL: **http://[yourhost]/http/28x28/host.com/distant/your/image.png**. This URL will download the image http://host.com/distant/your/image.png if exists and use it as master for resizing. The distant image is put in the original folder to forbid multiple downloads of the same image.

HTTP Status code are used to manage errors:

 - If the original file to be used for resize does not exists, the **HTTP 404** code is sent
 - If the server encounter a problem during image processing, the **HTTP 500** code is sent

If you find a bug, please submit an issue. If you want to contribute you're welcome!


## Licence

Copyright (c) 2013 **Stéphane HULARD**

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
