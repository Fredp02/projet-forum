# PHPSearch
| Keyword search, translation to another programming language (Python?), &amp; prepared statement support coming soon! [Vote on features](https://github.com/phpsearch/library/discussions/1) |
| ---

<img src="https://github.com/phpsearch/library/assets/76186054/60d446bd-f293-410a-9628-7f4c104c6c5b" width="300">

## Introduction

PHPSearch is a PHP search engine library with no dependencies. It's super simple and easy to use, with minimal programming knowledge required.

With PHPSearch, you don't have to worry about SQL Injection, advanced search algorithms, or even removing unimportant words in your search query. All of that boring stuff is covered by PHPSearch so you can focus on your project.

## Installation

### With Composer

You can install PHPSearch using Composer. This is the easiest way.

```
composer config minimum-stability dev
composer require mrfakename/phpsearch
```

### Without Composer

In the past, there were many packages that I wanted to use, but they were only available through Composer. This was extremely frustrating for me because I didn't want to use Composer (mainly because of the sheer numbers of files it created!)

1. Download the source code from GitHub
2. Copy `src/PHPSearch/PHPSearch.php` to your server
3. Include `PHPSearch.php` in your code.

Example:

```php
include_once 'PHPSearch.php';
```
## Usage

Example:

```php
include_once 'PHPSearch.php';
use mrfakename\PHPSearch;
# Create the connection!
$conn = mysqli_connect('localhost', 'root', '', 'links');

# Create the object!

$search = new PHPSearch();
$search->setQuery('PHPSearch'); // Not case-sensitive!
$search->setConn($conn);
// or $search = new PHPSearch('PHPSearch', $conn);
$res = $search->search('title', 'author', 'publisher', 'keywords'); // Returns mysqli_result
# Show the results
mysqli_fetch_assoc($res)) {
    echo "<p><b>$row[title]</b> by $row[author], published by $row[publisher].</p>";
}
```

### Create a Search object
```
new PHPSearch(string? query, mysqli? connection): PHPSearch
```
For example:
```php
$search = new PHPSearch();
```
or
```php
$search = new PHPSearch('test', $conn);
```
### Define the Query
If you already created a search object and passed the query and connection, you can skip this part.
```
PHPSearch->setQuery(string query): bool
```
For example:
```php
$search->setQuery('test');
```
### Define the Connection
If you already created a search object and passed the query and connection, you can skip this part.
```
PHPSearch->setConn(mysqli connection): bool
```
For example:
```php
$conn = mysqli_connect('localhost', 'root', '', 'books');
$search->setQuery($conn);
```
### Search
This is the most important function.

```
PHPSearch->search(string tablename, ...string tablecolumns): mysqli_result
```
For example:
```php
$res = $search->search('books', 'title', 'author', 'publisher');
```
### Showing the result

You could parse the `mysqli_result` this way but it's really up to you:
```php
while ($row = mysqli_fetch_assoc($res)) {
    echo $row['title'] . '<br>';
}
```

## DBSearch

Copyright &copy; 2023 [DBSearch](https://github.com/dbsearch). All rights reserved.

PHPSearch is an affiliate of DBSearch. DBSearch is creating open-sourced tools for searching databases. [Learn more](https://github.com/dbsearch)

<img src="https://github.com/phpsearch/library/assets/76186054/b15ed114-c51e-4161-bb5b-69b8329a2e98" width="100">
