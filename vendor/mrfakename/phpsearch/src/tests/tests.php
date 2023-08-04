<h1>Books matching "1984"</h1>
<?php
$conn = mysqli_connect('localhost', 'root', '', 'books');
$search = new PHPSearch();
$search = new PHPSearch('1984', $conn);
$res = $search->search('book', 'name', 'author', 'publisher');
while ($row = mysqli_fetch_assoc($res)) {
    echo '<b>' . $row['title'] . '</b> by ' . $row['author'] . '<br>'; // In real databases, use htmlspecialchars
}
