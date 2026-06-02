<?php
/**
 * HackathonAfrica LMS — Proctored Coding Interview: schema + question pool
 *
 * Creates the interview subsystem tables and seeds a pool of JavaScript
 * coding + debugging problems (with hidden test cases). Each candidate's
 * session randomly draws 3 coding + 10 debugging problems from this pool,
 * so no two candidates get the same set.
 *
 * Run ONCE: /database/setup_interview.php?key=hackafrica2026interview
 * Re-running is safe (it re-creates the pool; sessions/answers are untouched).
 * DELETE this file after running in production.
 */

define('INTERVIEW_SETUP_KEY', 'hackafrica2026interview');
if (($_GET['key'] ?? '') !== INTERVIEW_SETUP_KEY) {
    http_response_code(403);
    die('<h2>403 — Access Denied</h2><p>Add ?key=hackafrica2026interview to the URL.</p>');
}

require_once __DIR__ . '/../config/database.php';
$pdo = db();

header('Content-Type: text/html; charset=utf-8');
echo '<body style="font-family:system-ui;max-width:760px;margin:2rem auto;line-height:1.5">';
echo '<h2>Coding Interview Setup</h2>';

// ── Schema ───────────────────────────────────────────────────────────────────
$pdo->exec("CREATE TABLE IF NOT EXISTS interview_exercises (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kind TEXT NOT NULL CHECK(kind IN ('coding','debugging')),
    title TEXT NOT NULL,
    prompt TEXT NOT NULL,
    instructions TEXT,
    language TEXT NOT NULL DEFAULT 'javascript',
    entry_function TEXT NOT NULL,
    starter_code TEXT NOT NULL,
    reference_solution TEXT,
    test_cases_json TEXT NOT NULL,
    difficulty TEXT NOT NULL DEFAULT 'medium',
    points INTEGER NOT NULL DEFAULT 10,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS interview_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'in_progress' CHECK(status IN ('in_progress','submitted','reviewed')),
    exercise_ids_json TEXT NOT NULL,
    time_limit INTEGER NOT NULL DEFAULT 90,
    auto_score INTEGER,
    max_points INTEGER NOT NULL DEFAULT 0,
    review_decision TEXT NOT NULL DEFAULT 'pending' CHECK(review_decision IN ('pending','selected','rejected')),
    reviewer_id INTEGER,
    reviewer_notes TEXT,
    reviewed_at DATETIME,
    started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    submitted_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS interview_answers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    kind TEXT NOT NULL,
    submitted_code TEXT NOT NULL DEFAULT '',
    sample_passed INTEGER DEFAULT 0,
    sample_total INTEGER DEFAULT 0,
    admin_score INTEGER,
    admin_passed INTEGER,
    admin_total INTEGER,
    admin_feedback TEXT,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(session_id, exercise_id),
    FOREIGN KEY (session_id) REFERENCES interview_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES interview_exercises(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS interview_proctor_images (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    image_path TEXT NOT NULL,
    captured_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES interview_sessions(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS interview_events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    event_type TEXT NOT NULL,
    detail TEXT,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES interview_sessions(id) ON DELETE CASCADE
)");

echo "<p>✅ Tables ready (interview_exercises, interview_sessions, interview_answers, interview_proctor_images, interview_events)</p>";

// ── Reset pool (sessions/answers preserved) ──────────────────────────────────
$pdo->exec('DELETE FROM interview_exercises');

// Helper: build a problem row
function P(string $kind, string $title, string $prompt, string $instructions,
           string $entry, string $starter, string $solution, array $cases,
           string $difficulty = 'medium', int $points = 10): array {
    return compact('kind','title','prompt','instructions','entry','starter','solution','cases','difficulty','points');
}
// Helper: a test case
function T(array $args, $expected, bool $sample = false): array {
    return ['args' => $args, 'expected' => $expected, 'sample' => $sample];
}

$problems = [];

/* ═══════════════ CODING (implement the function) ═══════════════ */
$problems[] = P('coding', 'Sum of an Array',
    'Implement a function that returns the sum of all numbers in an array.',
    "1. sumArray([1,2,3]) returns 6\n2. An empty array returns 0\n3. Works with negative numbers",
    'sumArray',
    "function sumArray(arr) {\n  // TODO: return the sum of all numbers in arr\n}",
    "function sumArray(arr) {\n  let total = 0;\n  for (const n of arr) total += n;\n  return total;\n}",
    [T([[1,2,3]],6,true), T([[]],0,true), T([[-1,1]],0), T([[10,20,30,40]],100), T([[5]],5)],
    'easy');

$problems[] = P('coding', 'Reverse a String',
    'Implement a function that returns the input string reversed.',
    "1. reverseString('hello') returns 'olleh'\n2. An empty string returns ''",
    'reverseString',
    "function reverseString(s) {\n  // TODO: return s reversed\n}",
    "function reverseString(s) {\n  return s.split('').reverse().join('');\n}",
    [T(['hello'],'olleh',true), T([''],'',true), T(['ab'],'ba'), T(['racecar'],'racecar'), T(['HackA'],'AkcaH')],
    'easy');

$problems[] = P('coding', 'Palindrome Check',
    'Return true if the string reads the same forwards and backwards, ignoring case.',
    "1. isPalindrome('racecar') is true\n2. isPalindrome('Hello') is false\n3. Comparison is case-insensitive",
    'isPalindrome',
    "function isPalindrome(s) {\n  // TODO: return true if s is a palindrome (ignore case)\n}",
    "function isPalindrome(s) {\n  const t = s.toLowerCase();\n  return t === t.split('').reverse().join('');\n}",
    [T(['racecar'],true,true), T(['Hello'],false,true), T([''],true), T(['Noon'],true), T(['ab'],false)],
    'easy');

$problems[] = P('coding', 'Count the Vowels',
    'Return how many vowels (a, e, i, o, u) appear in the string, case-insensitive.',
    "1. countVowels('hello') returns 2\n2. countVowels('xyz') returns 0\n3. Counts upper and lower case",
    'countVowels',
    "function countVowels(s) {\n  // TODO: return the number of vowels in s\n}",
    "function countVowels(s) {\n  let n = 0;\n  for (const ch of s.toLowerCase()) if ('aeiou'.includes(ch)) n++;\n  return n;\n}",
    [T(['hello'],2,true), T(['xyz'],0,true), T(['AEIOU'],5), T([''],0), T(['programming'],3)],
    'easy');

$problems[] = P('coding', 'Maximum Value',
    'Return the largest number in a non-empty array.',
    "1. maxOf([3,7,2]) returns 7\n2. Works with all-negative arrays\n3. A single-element array returns that element",
    'maxOf',
    "function maxOf(arr) {\n  // TODO: return the largest number in arr\n}",
    "function maxOf(arr) {\n  let m = arr[0];\n  for (const n of arr) if (n > m) m = n;\n  return m;\n}",
    [T([[3,7,2]],7,true), T([[-5,-2,-9]],-2,true), T([[5]],5), T([[1,2,3,4]],4), T([[8,8,1]],8)],
    'easy');

$problems[] = P('coding', 'Factorial',
    'Return n! (the product of all integers from 1 to n). 0! is 1.',
    "1. factorial(5) returns 120\n2. factorial(0) returns 1\n3. factorial(1) returns 1",
    'factorial',
    "function factorial(n) {\n  // TODO: return n!\n}",
    "function factorial(n) {\n  let r = 1;\n  for (let i = 2; i <= n; i++) r *= i;\n  return r;\n}",
    [T([5],120,true), T([0],1,true), T([1],1), T([3],6), T([6],720)],
    'medium');

$problems[] = P('coding', 'FizzBuzz List',
    "Return an array from 1 to n where multiples of 3 become 'Fizz', multiples of 5 become 'Buzz', multiples of both become 'FizzBuzz', otherwise the number itself.",
    "1. Multiples of 3 -> 'Fizz'\n2. Multiples of 5 -> 'Buzz'\n3. Multiples of 15 -> 'FizzBuzz'\n4. Otherwise the number",
    'fizzbuzz',
    "function fizzbuzz(n) {\n  // TODO: return the FizzBuzz array for 1..n\n}",
    "function fizzbuzz(n) {\n  const out = [];\n  for (let i = 1; i <= n; i++) {\n    if (i % 15 === 0) out.push('FizzBuzz');\n    else if (i % 3 === 0) out.push('Fizz');\n    else if (i % 5 === 0) out.push('Buzz');\n    else out.push(i);\n  }\n  return out;\n}",
    [T([5],[1,2,'Fizz',4,'Buzz'],true), T([3],[1,2,'Fizz'],true), T([15],[1,2,'Fizz',4,'Buzz','Fizz',7,8,'Fizz','Buzz',11,'Fizz',13,14,'FizzBuzz']), T([1],[1])],
    'medium');

$problems[] = P('coding', 'Unique Values',
    'Return a new array with duplicates removed, preserving first-seen order.',
    "1. uniqueValues([1,2,2,3,1]) returns [1,2,3]\n2. Order of first appearance is kept\n3. An empty array returns []",
    'uniqueValues',
    "function uniqueValues(arr) {\n  // TODO: return arr without duplicates, order preserved\n}",
    "function uniqueValues(arr) {\n  const out = [];\n  for (const x of arr) if (!out.includes(x)) out.push(x);\n  return out;\n}",
    [T([[1,2,2,3,1]],[1,2,3],true), T([[]],[],true), T([['a','a','b']],['a','b']), T([[5,5,5]],[5])],
    'medium');

$problems[] = P('coding', 'Title Case',
    'Capitalise the first letter of every word; lower-case the rest. Words are separated by single spaces.',
    "1. titleCase('hello world') returns 'Hello World'\n2. An empty string returns ''",
    'titleCase',
    "function titleCase(s) {\n  // TODO: capitalise the first letter of each word\n}",
    "function titleCase(s) {\n  return s.split(' ').map(w => w ? w[0].toUpperCase() + w.slice(1).toLowerCase() : w).join(' ');\n}",
    [T(['hello world'],'Hello World',true), T([''],'',true), T(['the QUICK brown'],'The Quick Brown'), T(['a'],'A')],
    'medium');

$problems[] = P('coding', 'Nth Fibonacci',
    'Return the nth Fibonacci number (0-indexed: 0, 1, 1, 2, 3, 5, 8, ...).',
    "1. fibonacci(0) returns 0\n2. fibonacci(1) returns 1\n3. fibonacci(7) returns 13",
    'fibonacci',
    "function fibonacci(n) {\n  // TODO: return the nth Fibonacci number\n}",
    "function fibonacci(n) {\n  let a = 0, b = 1;\n  for (let i = 0; i < n; i++) { const t = a + b; a = b; b = t; }\n  return a;\n}",
    [T([0],0,true), T([1],1,true), T([2],1), T([7],13), T([10],55)],
    'hard');

/* ═══════════════ DEBUGGING (fix the broken function) ═══════════════ */
$problems[] = P('debugging', 'Fix: Array Sum',
    'This function should add up all numbers in the array, but the result is wrong (NaN).',
    "1. The total starts at 0\n2. Every element is added exactly once\n3. The loop does not read past the end of the array",
    'sumTo',
    "function sumTo(arr) {\n  let total;\n  for (let i = 1; i <= arr.length; i++) {\n    total += arr[i];\n  }\n  return total;\n}",
    "function sumTo(arr) {\n  let total = 0;\n  for (let i = 0; i < arr.length; i++) {\n    total += arr[i];\n  }\n  return total;\n}",
    [T([[1,2,3]],6,true), T([[10,20]],30,true), T([[5]],5), T([[4,8,15,16,23,42]],108)],
    'easy');

$problems[] = P('debugging', 'Fix: Is Even',
    'This function should return true for even numbers, but it returns the opposite.',
    "1. isEven(4) is true\n2. isEven(3) is false\n3. isEven(0) is true",
    'isEven',
    "function isEven(n) {\n  return n % 2 === 1;\n}",
    "function isEven(n) {\n  return n % 2 === 0;\n}",
    [T([4],true,true), T([3],false,true), T([0],true), T([7],false), T([10],true)],
    'easy');

$problems[] = P('debugging', 'Fix: Find Maximum',
    'This function fails when every number is negative because it starts comparing against 0.',
    "1. findMax([3,7,2]) returns 7\n2. Works when all numbers are negative\n3. A single-element array returns that element",
    'findMax',
    "function findMax(arr) {\n  let max = 0;\n  for (let i = 0; i < arr.length; i++) {\n    if (arr[i] > max) max = arr[i];\n  }\n  return max;\n}",
    "function findMax(arr) {\n  let max = arr[0];\n  for (let i = 1; i < arr.length; i++) {\n    if (arr[i] > max) max = arr[i];\n  }\n  return max;\n}",
    [T([[3,7,2]],7,true), T([[-5,-2,-9]],-2,true), T([[-1]],-1), T([[1,9,4]],9)],
    'easy');

$problems[] = P('debugging', 'Fix: Product of Array',
    'This function should multiply all the numbers together, but it always returns 0.',
    "1. product([2,3,4]) returns 24\n2. product([5]) returns 5\n3. product([1,1,1]) returns 1",
    'product',
    "function product(arr) {\n  let result = 0;\n  for (const n of arr) result *= n;\n  return result;\n}",
    "function product(arr) {\n  let result = 1;\n  for (const n of arr) result *= n;\n  return result;\n}",
    [T([[2,3,4]],24,true), T([[5]],5,true), T([[1,1,1]],1), T([[2,2,2,2]],16)],
    'easy');

$problems[] = P('debugging', 'Fix: Last Item',
    'This function should return the last element of the array, but it returns undefined.',
    "1. lastItem([1,2,3]) returns 3\n2. lastItem(['a']) returns 'a'",
    'lastItem',
    "function lastItem(arr) {\n  return arr[arr.length];\n}",
    "function lastItem(arr) {\n  return arr[arr.length - 1];\n}",
    [T([[1,2,3]],3,true), T([['a']],'a',true), T([[9,8,7,6]],6), T([[42]],42)],
    'easy');

$problems[] = P('debugging', 'Fix: Count a Character',
    'This function should count how many times ch appears in s, but it counts every character.',
    "1. countChar('banana','a') returns 3\n2. countChar('hello','l') returns 2\n3. A character not present returns 0",
    'countChar',
    "function countChar(s, ch) {\n  let count = 0;\n  for (let i = 0; i < s.length; i++) {\n    if (s[i] = ch) count++;\n  }\n  return count;\n}",
    "function countChar(s, ch) {\n  let count = 0;\n  for (let i = 0; i < s.length; i++) {\n    if (s[i] === ch) count++;\n  }\n  return count;\n}",
    [T(['banana','a'],3,true), T(['hello','l'],2,true), T(['abc','z'],0), T(['aaa','a'],3)],
    'medium');

$problems[] = P('debugging', 'Fix: Average',
    'This function should return the average, but operator precedence makes it wrong.',
    "1. average([2,4,6]) returns 4\n2. average([10,20]) returns 15\n3. average([5]) returns 5",
    'average',
    "function average(arr) {\n  let sum = 0;\n  for (const n of arr) sum += n;\n  return sum / arr.length - 1;\n}",
    "function average(arr) {\n  let sum = 0;\n  for (const n of arr) sum += n;\n  return sum / arr.length;\n}",
    [T([[2,4,6]],4,true), T([[10,20]],15,true), T([[5]],5), T([[3,3,3,3]],3)],
    'medium');

$problems[] = P('debugging', 'Fix: Capitalize First',
    'This function should upper-case only the first letter, but it duplicates it.',
    "1. capitalize('hello') returns 'Hello'\n2. capitalize('a') returns 'A'",
    'capitalize',
    "function capitalize(s) {\n  return s[0].toUpperCase() + s.slice(0);\n}",
    "function capitalize(s) {\n  return s[0].toUpperCase() + s.slice(1);\n}",
    [T(['hello'],'Hello',true), T(['a'],'A',true), T(['world'],'World'), T(['xyz'],'Xyz')],
    'medium');

$problems[] = P('debugging', 'Fix: Fahrenheit to Celsius',
    'The conversion formula is grouped wrong, so the result is incorrect.',
    "1. toCelsius(32) returns 0\n2. toCelsius(212) returns 100\n3. toCelsius(50) returns 10",
    'toCelsius',
    "function toCelsius(f) {\n  return f - 32 * 5 / 9;\n}",
    "function toCelsius(f) {\n  return (f - 32) * 5 / 9;\n}",
    [T([32],0,true), T([212],100,true), T([50],10), T([14],-10)],
    'medium');

$problems[] = P('debugging', 'Fix: Double All',
    'This function should double every number, but it adds 2 instead.',
    "1. doubleAll([1,2,3]) returns [2,4,6]\n2. doubleAll([0]) returns [0]",
    'doubleAll',
    "function doubleAll(arr) {\n  return arr.map(n => n + 2);\n}",
    "function doubleAll(arr) {\n  return arr.map(n => n * 2);\n}",
    [T([[1,2,3]],[2,4,6],true), T([[0]],[0],true), T([[5,10]],[10,20]), T([[-3]],[-6])],
    'easy');

$problems[] = P('debugging', 'Fix: Range 1..n',
    'This function should return [1,2,...,n], but it leaves off the last number.',
    "1. rangeTo(5) returns [1,2,3,4,5]\n2. rangeTo(1) returns [1]\n3. The end value n is included",
    'rangeTo',
    "function rangeTo(n) {\n  const out = [];\n  for (let i = 1; i < n; i++) out.push(i);\n  return out;\n}",
    "function rangeTo(n) {\n  const out = [];\n  for (let i = 1; i <= n; i++) out.push(i);\n  return out;\n}",
    [T([5],[1,2,3,4,5],true), T([1],[1],true), T([3],[1,2,3]), T([0],[])],
    'medium');

$problems[] = P('debugging', 'Fix: Prime Check',
    'This function wrongly reports 1 as prime and mishandles small numbers.',
    "1. isPrime(1) is false\n2. isPrime(2) is true\n3. isPrime(9) is false\n4. isPrime(7) is true",
    'isPrime',
    "function isPrime(n) {\n  for (let i = 2; i < n; i++) {\n    if (n % i === 0) return false;\n  }\n  return true;\n}",
    "function isPrime(n) {\n  if (n < 2) return false;\n  for (let i = 2; i < n; i++) {\n    if (n % i === 0) return false;\n  }\n  return true;\n}",
    [T([1],false,true), T([2],true,true), T([9],false), T([7],true), T([0],false)],
    'hard');

$problems[] = P('debugging', 'Fix: Sum of Digits',
    'This function should add the digits of a number, but it concatenates them as text.',
    "1. sumDigits(123) returns 6\n2. sumDigits(9) returns 9\n3. sumDigits(101) returns 2",
    'sumDigits',
    "function sumDigits(n) {\n  return String(n).split('').reduce((a, b) => a + b, 0);\n}",
    "function sumDigits(n) {\n  return String(n).split('').reduce((a, b) => a + Number(b), 0);\n}",
    [T([123],6,true), T([9],9,true), T([101],2), T([444],12)],
    'medium');

$problems[] = P('debugging', 'Fix: Word Count',
    'This function should count words, but it miscounts empty strings and extra spaces.',
    "1. countWords('hi there') returns 2\n2. countWords('') returns 0\n3. countWords('one') returns 1",
    'countWords',
    "function countWords(s) {\n  return s.split(' ').length;\n}",
    "function countWords(s) {\n  return s.trim().split(/\\s+/).filter(Boolean).length;\n}",
    [T(['hi there'],2,true), T([''],0,true), T(['one'],1), T(['a b c'],3)],
    'hard');

$problems[] = P('debugging', 'Fix: Remove Duplicates',
    'This function should keep only the first occurrence of each value, but the condition is wrong.',
    "1. removeDuplicates([1,1,2]) returns [1,2]\n2. removeDuplicates([3,3,3]) returns [3]\n3. Order is preserved",
    'removeDuplicates',
    "function removeDuplicates(arr) {\n  const out = [];\n  for (const x of arr) {\n    if (out.indexOf(x)) out.push(x);\n  }\n  return out;\n}",
    "function removeDuplicates(arr) {\n  const out = [];\n  for (const x of arr) {\n    if (out.indexOf(x) === -1) out.push(x);\n  }\n  return out;\n}",
    [T([[1,1,2]],[1,2],true), T([[3,3,3]],[3],true), T([[1,2,2,3]],[1,2,3]), T([[4,5,4]],[4,5]), T([[5,5,6,5]],[5,6])],
    'hard');

$problems[] = P('debugging', 'Fix: Grade Letter',
    'The grade boundaries are wrong: scores on the boundary get the wrong letter.',
    "1. gradeLetter(90) is 'A'\n2. gradeLetter(80) is 'B'\n3. gradeLetter(70) is 'C'\n4. Below 70 is 'F'",
    'gradeLetter',
    "function gradeLetter(score) {\n  if (score > 90) return 'A';\n  else if (score > 80) return 'B';\n  else if (score > 70) return 'C';\n  else return 'F';\n}",
    "function gradeLetter(score) {\n  if (score >= 90) return 'A';\n  else if (score >= 80) return 'B';\n  else if (score >= 70) return 'C';\n  else return 'F';\n}",
    [T([90],'A',true), T([80],'B',true), T([70],'C'), T([65],'F'), T([95],'A')],
    'medium');

$problems[] = P('debugging', 'Fix: Repeat String',
    'This function should repeat s exactly n times, but it repeats one extra time.',
    "1. repeatStr('ab',3) returns 'ababab'\n2. repeatStr('x',0) returns ''",
    'repeatStr',
    "function repeatStr(s, n) {\n  let out = '';\n  for (let i = 0; i <= n; i++) out += s;\n  return out;\n}",
    "function repeatStr(s, n) {\n  let out = '';\n  for (let i = 0; i < n; i++) out += s;\n  return out;\n}",
    [T(['ab',3],'ababab',true), T(['x',0],'',true), T(['-',5],'-----'), T(['ha',2],'haha')],
    'medium');

$problems[] = P('debugging', 'Fix: Clamp a Value',
    'This function should clamp x between lo and hi, but the return values are swapped.',
    "1. clamp(5,0,10) returns 5\n2. clamp(-3,0,10) returns 0\n3. clamp(15,0,10) returns 10",
    'clamp',
    "function clamp(x, lo, hi) {\n  if (x < lo) return hi;\n  if (x > hi) return lo;\n  return x;\n}",
    "function clamp(x, lo, hi) {\n  if (x < lo) return lo;\n  if (x > hi) return hi;\n  return x;\n}",
    [T([5,0,10],5,true), T([-3,0,10],0,true), T([15,0,10],10), T([7,1,5],5)],
    'medium');

$problems[] = P('debugging', 'Fix: Second Largest',
    'This function should return the second largest number, but the default sort orders numbers as text.',
    "1. secondLargest([3,1,2]) returns 2\n2. secondLargest([10,5,8]) returns 8\n3. Works with multi-digit numbers",
    'secondLargest',
    "function secondLargest(arr) {\n  const sorted = arr.slice().sort();\n  return sorted[sorted.length - 2];\n}",
    "function secondLargest(arr) {\n  const sorted = arr.slice().sort((a, b) => a - b);\n  return sorted[sorted.length - 2];\n}",
    [T([[3,1,2]],2,true), T([[10,5,8]],8,true), T([[1,2,3,4]],3), T([[100,20,9]],20)],
    'hard');

$problems[] = P('debugging', 'Fix: Percentage',
    'This function should return a whole-number percentage, but it forgets to multiply by 100.',
    "1. percent(1,4) returns 25\n2. percent(1,2) returns 50\n3. percent(3,3) returns 100",
    'percent',
    "function percent(part, whole) {\n  return Math.round(part / whole);\n}",
    "function percent(part, whole) {\n  return Math.round((part / whole) * 100);\n}",
    [T([1,4],25,true), T([1,2],50,true), T([3,3],100), T([1,8],13)],
    'medium');

$problems[] = P('debugging', 'Fix: Merge Two Arrays',
    'This function should return a new combined array, but it mutates the first one and returns its length.',
    "1. merge([1,2],[3,4]) returns [1,2,3,4]\n2. merge([],[5]) returns [5]\n3. The inputs are not modified",
    'merge',
    "function merge(a, b) {\n  return a.push(...b);\n}",
    "function merge(a, b) {\n  return [...a, ...b];\n}",
    [T([[1,2],[3,4]],[1,2,3,4],true), T([[],[5]],[5],true), T([[7],[8,9]],[7,8,9])],
    'medium');

$problems[] = P('debugging', 'Fix: Truthy Filter',
    'This function should keep only even numbers, but it keeps odd ones.',
    "1. evens([1,2,3,4]) returns [2,4]\n2. evens([1,3,5]) returns []",
    'evens',
    "function evens(arr) {\n  return arr.filter(n => n % 2);\n}",
    "function evens(arr) {\n  return arr.filter(n => n % 2 === 0);\n}",
    [T([[1,2,3,4]],[2,4],true), T([[1,3,5]],[],true), T([[2,4,6]],[2,4,6]), T([[0,1]],[0])],
    'easy');

// ── Insert pool ──────────────────────────────────────────────────────────────
$ins = $pdo->prepare(
    'INSERT INTO interview_exercises
        (kind, title, prompt, instructions, language, entry_function, starter_code, reference_solution, test_cases_json, difficulty, points)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$coding = 0; $debugging = 0;
foreach ($problems as $p) {
    $testJson = json_encode(['entry' => $p['entry'], 'cases' => $p['cases']]);
    $ins->execute([
        $p['kind'], $p['title'], $p['prompt'], $p['instructions'], 'javascript',
        $p['entry'], $p['starter'], $p['solution'], $testJson, $p['difficulty'], $p['points'],
    ]);
    $p['kind'] === 'coding' ? $coding++ : $debugging++;
}

echo "<p>✅ Seeded pool: <strong>{$coding}</strong> coding + <strong>{$debugging}</strong> debugging problems</p>";
echo "<p>Each candidate session draws " . 3 . " coding + " . 10 . " debugging at random and shuffles them.</p>";
echo "<hr><p><strong>Done.</strong></p>";
echo '<p style="color:#c00">⚠ Delete this file (database/setup_interview.php) after running in production.</p>';
echo '</body>';
