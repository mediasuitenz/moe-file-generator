MOE File Generator
==================

Setup
-----

Create a copy of **config-example.php** and name it **config.php**

Edit moeFilePath value in **config.php** to be the path of a writeable directory for storing .moe files

Ensure tables exist as described in ``schema.sql`` for tracking .moe files.

Insert rows into the database table **'roll_return_period'** for the current roll return, ensuring month, year and mode are set correctly.

Add database configuration to **config.php**

Run ``composer install`` to install dependencies

Require MOEFileGenerator in your code and call generateMOE to create .moe file

e.g.

```php
require_once 'moe-file-generator/MOEFileGenerator.php';
MOEFileGenerator\MOEFileGenerator::generateMOE($data);
```

**$data** is an array with two keys, 'meta' and 'students'

**'meta'** is an array with the following structure:

```php
'meta' => array(
  'smsName' => 'Linc-Ed',
  'smsVersion' => '2.8',
  'schoolNumber' => '12345',
  'collectionMonth' => 'M',
  'collectionYear' => '2015',
  'enrolmentScheme' => 'N',
  'enrolmentSchemeDate' => '(if one exists 20141003 - otherwise dont include this field)',
  'isDraft' => false,
  'approver' => 'RoseKennedy'
)
```

**'students'** is an array of student arrays - all values must be already validated


To retrieve a list of .moe files for a specific roll return period call
```php
MOEFileGenerator\MOEFileGenerator::getMOEFiles('M', '2015', false);
```
Where M is the roll return month for March, 2015 is the year and false is if the roll return period is for drafts.
