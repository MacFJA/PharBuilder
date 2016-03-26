WIP

```
... The content of your composer.json file
"extra": {
    "phar-builder": {
        "compression": "GZip",
        "name": "application.phar",
        "output-dir": "../",
        "entry-point": "./index.php",
        "include": ["bin","js","css"],
        "include-dev": false,
        "events": {
            "build.before" : "git describe --tags > bin/version.txt"
            "build.after": [
                "rm bin/version.txt",
                "chmod +x ../application.phar"
            ]
        }
    }
}
```