MOE File Generator
==================

Setup
-----

Create a copy of config-example.php and name it config.php

Edit moeFilePath value in config.php

Require MOEFileGenerator in your code and call generateMOE to create .moe file

e.g.

```php
require_once 'MOEFileGenerator/MOEFileGenerator.php';
MOEFileGenerator\MOEFileGenerator::generateMOE($data);
```
