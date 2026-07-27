<?php
/**
 * Participant Feedback — schema + data helpers.
 *
 * The three feedback forms (Feeding, Accommodation, Training Delivery) are
 * defined once here as data. Everything else — the public form rendering, the
 * server-side validation, the admin table and the Excel/CSV export columns —
 * is generated from this definition, so adding or renaming a question is a
 * single-file change.
 *
 * Field types:
 *   rating     1–5 pills, with 'low'/'high' end labels
 *   emoji      one-of options rendered as emoji cards  (options: value => [emoji, label])
 *   checkbox   multi-select                            (options: value => label)
 *   textarea   free text
 *   text       short free text
 *
 * Answers are stored one row per question in `feedback_answers`, keyed by the
 * question key below. Multi-select answers are stored joined with '; '.
 */

require_once __DIR__ . '/functions.php';

const FEEDBACK_CATEGORIES = ['feeding', 'accommodation', 'training'];

function feedback_forms(): array {
    static $forms = null;
    if ($forms !== null) return $forms;

    $forms = [
        // ─────────────────────────────── FEEDING ───────────────────────────────
        'feeding' => [
            'label'   => 'Feeding',
            'icon'    => '🍽️',
            'title'   => 'Feeding Feedback',
            'intro'   => 'Help us improve the quality and experience of meals at camp.',
            'success' => 'Your response on feeding has been recorded. We appreciate your honesty.',
            'sections' => [
                'Meal Quality' => [
                    'quality' => [
                        'label' => 'How would you rate the overall quality of meals?',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Poor', 'high' => 'Excellent',
                    ],
                    'variety' => [
                        'label' => 'How satisfied are you with the food variety?',
                        'type' => 'emoji', 'required' => true,
                        'options' => [
                            'very-unsatisfied' => ['😞', 'Very Unsatisfied'],
                            'unsatisfied'      => ['😐', 'Unsatisfied'],
                            'neutral'          => ['🙂', 'Okay'],
                            'satisfied'        => ['😊', 'Satisfied'],
                            'very-satisfied'   => ['😄', 'Very Satisfied'],
                        ],
                    ],
                    'portion' => [
                        'label' => 'Rate the portion sizes',
                        'type' => 'rating', 'required' => false,
                        'low' => 'Too Small', 'high' => 'Just Right',
                    ],
                ],
                'Dining Experience' => [
                    'punctuality' => [
                        'label' => 'How was the punctuality of meal service?',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Always Late', 'high' => 'Always On Time',
                    ],
                    'cleanliness' => [
                        'label' => 'Rate cleanliness of the dining area',
                        'type' => 'rating', 'required' => false,
                        'low' => 'Poor', 'high' => 'Excellent',
                    ],
                    'improve_meals' => [
                        'label' => 'Which meal(s) need the most improvement?',
                        'type' => 'checkbox', 'required' => false,
                        'options' => [
                            'breakfast' => 'Breakfast',
                            'lunch'     => 'Lunch',
                            'dinner'    => 'Dinner',
                            'snacks'    => 'Snacks / Refreshments',
                        ],
                    ],
                ],
                'Your Thoughts' => [
                    'enjoy_most' => [
                        'label' => 'What do you enjoy most about the meals?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'e.g. Variety, taste, freshness...',
                    ],
                    'improvements' => [
                        'label' => 'What would you like to see improved?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'Be as specific as you like — we want to get this right.',
                    ],
                    'dietary_needs' => [
                        'label' => 'Any dietary needs or concerns we should know about?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'e.g. Allergies, preferences, portions...',
                    ],
                ],
            ],
        ],

        // ──────────────────────────── ACCOMMODATION ────────────────────────────
        'accommodation' => [
            'label'   => 'Accommodation',
            'icon'    => '🏠',
            'title'   => 'Accommodation Feedback',
            'intro'   => 'Let us know how your residential experience has been so far.',
            'success' => 'Your accommodation feedback has been recorded. We\'ll act on it promptly.',
            'sections' => [
                'Living Space' => [
                    'comfort' => [
                        'label' => 'How comfortable is your sleeping area?',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Very Uncomfortable', 'high' => 'Very Comfortable',
                    ],
                    'cleanliness' => [
                        'label' => 'Rate the cleanliness of your living space',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Very Dirty', 'high' => 'Spotless',
                    ],
                    'ventilation' => [
                        'label' => 'How is the ventilation and air quality in your room?',
                        'type' => 'emoji', 'required' => false,
                        'options' => [
                            'poor'  => ['😮‍💨', 'Poor'],
                            'fair'  => ['🙂', 'Fair'],
                            'good'  => ['😊', 'Good'],
                            'great' => ['😄', 'Great'],
                        ],
                    ],
                ],
                'Facilities' => [
                    'bathrooms' => [
                        'label' => 'Rate access to bathroom / toilet facilities',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Very Poor', 'high' => 'Excellent',
                    ],
                    'power' => [
                        'label' => 'Rate availability of electricity / power supply',
                        'type' => 'rating', 'required' => false,
                        'low' => 'Rarely Available', 'high' => 'Consistent',
                    ],
                    'urgent_facilities' => [
                        'label' => 'Which facilities need urgent attention?',
                        'type' => 'checkbox', 'required' => false,
                        'options' => [
                            'toilets'  => 'Toilets / Bathrooms',
                            'beds'     => 'Beds / Bedding',
                            'power'    => 'Power / Charging Points',
                            'water'    => 'Water Supply',
                            'lighting' => 'Lighting',
                            'wifi'     => 'Internet / Wi-Fi in rooms',
                        ],
                    ],
                ],
                'Safety & Security' => [
                    'safety' => [
                        'label' => 'How safe do you feel within the camp premises?',
                        'type' => 'emoji', 'required' => true,
                        'options' => [
                            'unsafe'    => ['😟', 'Unsafe'],
                            'neutral'   => ['😐', 'Neutral'],
                            'safe'      => ['🙂', 'Safe'],
                            'very-safe' => ['😊', 'Very Safe'],
                        ],
                    ],
                ],
                'Your Thoughts' => [
                    'appreciate_most' => [
                        'label' => 'What do you appreciate most about your accommodation?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'Tell us what\'s working well...',
                    ],
                    'needs_fixing' => [
                        'label' => 'What needs to be fixed or improved?',
                        'type' => 'textarea', 'required' => true,
                        'placeholder' => 'Please be specific — this helps us act quickly.',
                    ],
                    'other_concerns' => [
                        'label' => 'Any other concerns about your living conditions?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'Anything else on your mind...',
                    ],
                ],
            ],
        ],

        // ───────────────────────── TRAINING DELIVERY ──────────────────────────
        'training' => [
            'label'   => 'Training Delivery',
            'icon'    => '💻',
            'title'   => 'Training Delivery Feedback',
            'intro'   => 'Share your experience of the teaching, curriculum, and learning environment.',
            'success' => 'Your training feedback has been received. We\'re committed to making this the best experience possible.',
            'sections' => [
                '' => [
                    'session' => [
                        'label' => 'Session / Module (optional)',
                        'type' => 'text', 'required' => false,
                        'placeholder' => 'e.g. Week 1 — Python Fundamentals',
                    ],
                ],
                'Facilitator & Teaching' => [
                    'instruction' => [
                        'label' => 'How would you rate the quality of instruction?',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Very Poor', 'high' => 'Excellent',
                    ],
                    'clarity' => [
                        'label' => 'How clearly were concepts explained?',
                        'type' => 'emoji', 'required' => true,
                        'options' => [
                            'very-unclear' => ['😕', 'Very Unclear'],
                            'unclear'      => ['😐', 'Unclear'],
                            'clear'        => ['🙂', 'Clear'],
                            'very-clear'   => ['😊', 'Very Clear'],
                        ],
                    ],
                    'engagement' => [
                        'label' => 'How well did the facilitator engage participants?',
                        'type' => 'rating', 'required' => false,
                        'low' => 'Not at All', 'high' => 'Very Engaging',
                    ],
                    'support' => [
                        'label' => 'Did the facilitator make time for questions and support?',
                        'type' => 'emoji', 'required' => false,
                        'options' => [
                            'never'     => ['❌', 'Never'],
                            'sometimes' => ['🟡', 'Sometimes'],
                            'often'     => ['✅', 'Often'],
                            'always'    => ['🌟', 'Always'],
                        ],
                    ],
                ],
                'Curriculum & Content' => [
                    'relevance' => [
                        'label' => 'How relevant is the curriculum to your career goals?',
                        'type' => 'rating', 'required' => true,
                        'low' => 'Not Relevant', 'high' => 'Highly Relevant',
                    ],
                    'pace' => [
                        'label' => 'How is the pace of training?',
                        'type' => 'emoji', 'required' => false,
                        'options' => [
                            'too-slow'   => ['🐢', 'Too Slow'],
                            'just-right' => ['✅', 'Just Right'],
                            'too-fast'   => ['🚀', 'Too Fast'],
                        ],
                    ],
                    'needs_depth' => [
                        'label' => 'Which areas of the curriculum need more depth?',
                        'type' => 'checkbox', 'required' => false,
                        'options' => [
                            'fundamentals'  => 'Fundamentals & Core Concepts',
                            'projects'      => 'Hands-on Projects & Practice',
                            'ai'            => 'AI & Agentic Systems',
                            'collaboration' => 'Collaboration & Team Work',
                            'assessments'   => 'Assessments & Feedback',
                            'career'        => 'Career Preparation',
                        ],
                    ],
                ],
                'Learning Environment' => [
                    'environment' => [
                        'label' => 'Rate the overall classroom environment (space, tools, focus)',
                        'type' => 'rating', 'required' => false,
                        'low' => 'Poor', 'high' => 'Excellent',
                    ],
                    'confidence' => [
                        'label' => 'How confident do you feel in applying what you\'ve learned?',
                        'type' => 'emoji', 'required' => false,
                        'options' => [
                            'not-confident'  => ['😟', 'Not Confident'],
                            'somewhat'       => ['🙂', 'Somewhat'],
                            'confident'      => ['😊', 'Confident'],
                            'very-confident' => ['💪', 'Very Confident'],
                        ],
                    ],
                ],
                'Open Feedback' => [
                    'most_valuable' => [
                        'label' => 'What has been the most valuable part of training so far?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'e.g. A topic, a project, a facilitator\'s approach...',
                    ],
                    'make_better' => [
                        'label' => 'What would make the training experience better?',
                        'type' => 'textarea', 'required' => true,
                        'placeholder' => 'Be honest — your feedback shapes the program.',
                    ],
                    'other_comments' => [
                        'label' => 'Any other comments for the training team?',
                        'type' => 'textarea', 'required' => false,
                        'placeholder' => 'Anything else you\'d like us to know...',
                    ],
                ],
            ],
        ],
    ];

    return $forms;
}

function feedback_form(string $category): ?array {
    return feedback_forms()[$category] ?? null;
}

/** All questions of a form, flattened to key => definition (section order preserved). */
function feedback_fields(string $category): array {
    $form = feedback_form($category);
    if (!$form) return [];
    $flat = [];
    foreach ($form['sections'] as $fields) {
        foreach ($fields as $key => $def) $flat[$key] = $def;
    }
    return $flat;
}

/** Human-readable rendering of a stored answer (for admin + export). */
function feedback_display_answer(array $field, ?string $value): string {
    $value = (string)($value ?? '');
    if ($value === '') return '';

    switch ($field['type']) {
        case 'rating':
            return $value . '/5';
        case 'emoji':
            return $field['options'][$value][1] ?? $value;
        case 'checkbox':
            $out = [];
            foreach (explode('; ', $value) as $v) {
                $out[] = $field['options'][$v] ?? $v;
            }
            return implode('; ', $out);
        default:
            return $value;
    }
}

/**
 * Validate a submitted form. Returns [cleanAnswers, errors].
 * Answers are returned as key => string (multi-select joined by '; ').
 */
function feedback_validate(string $category, array $post): array {
    $fields = feedback_fields($category);
    $answers = [];
    $errors  = [];

    foreach ($fields as $key => $def) {
        $raw = $post['q'][$key] ?? null;

        if ($def['type'] === 'checkbox') {
            $picked = is_array($raw) ? $raw : [];
            $picked = array_values(array_intersect(array_keys($def['options']), $picked));
            $value = implode('; ', $picked);
        } else {
            $value = is_string($raw) ? trim($raw) : '';
            if ($value !== '') {
                if ($def['type'] === 'rating' && !in_array($value, ['1', '2', '3', '4', '5'], true)) {
                    $value = '';
                } elseif ($def['type'] === 'emoji' && !isset($def['options'][$value])) {
                    $value = '';
                } elseif (in_array($def['type'], ['text', 'textarea'], true)) {
                    $value = mb_substr($value, 0, 5000);
                }
            }
        }

        if (!empty($def['required']) && $value === '') {
            $errors[$key] = $def['label'] . ' is required.';
        }
        $answers[$key] = $value;
    }

    return [$answers, $errors];
}

/**
 * Persist one feedback submission. Returns the new response id.
 *
 * Anonymity is honoured strictly: when $anonymous is true neither the typed
 * name nor the logged-in user id is stored, so a response can never be traced
 * back to a participant.
 */
function feedback_save(string $category, array $answers, ?string $name, string $feedbackDate, bool $anonymous, ?int $userId): int {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO feedback_responses (category, user_id, respondent_name, is_anonymous, feedback_date)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $category,
            $anonymous ? null : $userId,
            $anonymous ? null : ($name !== '' ? $name : null),
            $anonymous ? 1 : 0,
            $feedbackDate,
        ]);
        $responseId = (int)$pdo->lastInsertId();

        $ins = $pdo->prepare('INSERT INTO feedback_answers (response_id, question_key, answer_value) VALUES (?, ?, ?)');
        foreach ($answers as $key => $value) {
            if ($value === '') continue;   // keep unanswered optional questions out of the table
            $ins->execute([$responseId, $key, $value]);
        }

        $pdo->commit();
        return $responseId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

/** Responses for a category (or all), newest first, with answers attached. */
function feedback_get_responses(string $category = 'all', int $limit = 0, int $offset = 0): array {
    $where = '';
    $params = [];
    if (in_array($category, FEEDBACK_CATEGORIES, true)) {
        $where = 'WHERE r.category = ?';
        $params[] = $category;
    }
    $sql = "SELECT r.*, u.name AS user_name, u.email AS user_email
            FROM feedback_responses r
            LEFT JOIN users u ON u.id = r.user_id
            $where
            ORDER BY r.submitted_at DESC, r.id DESC";
    if ($limit > 0) $sql .= " LIMIT $limit OFFSET $offset";

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    if (!$rows) return [];

    $ids = array_column($rows, 'id');
    $in  = implode(',', array_fill(0, count($ids), '?'));
    $ans = db()->prepare("SELECT response_id, question_key, answer_value FROM feedback_answers WHERE response_id IN ($in)");
    $ans->execute($ids);

    $byResponse = [];
    foreach ($ans->fetchAll() as $a) {
        $byResponse[$a['response_id']][$a['question_key']] = $a['answer_value'];
    }
    foreach ($rows as &$r) {
        $r['answers'] = $byResponse[$r['id']] ?? [];
    }
    return $rows;
}

function feedback_count(string $category = 'all'): int {
    if (in_array($category, FEEDBACK_CATEGORIES, true)) {
        $stmt = db()->prepare('SELECT COUNT(*) FROM feedback_responses WHERE category = ?');
        $stmt->execute([$category]);
        return (int)$stmt->fetchColumn();
    }
    return (int)db()->query('SELECT COUNT(*) FROM feedback_responses')->fetchColumn();
}

/** Average score (out of 5) for every rating question of a category. */
function feedback_rating_averages(string $category): array {
    $out = [];
    foreach (feedback_fields($category) as $key => $def) {
        if ($def['type'] !== 'rating') continue;
        $stmt = db()->prepare(
            "SELECT AVG(CAST(a.answer_value AS REAL)) AS avg_score, COUNT(*) AS n
             FROM feedback_answers a
             JOIN feedback_responses r ON r.id = a.response_id
             WHERE r.category = ? AND a.question_key = ? AND a.answer_value <> ''"
        );
        $stmt->execute([$category, $key]);
        $row = $stmt->fetch();
        $out[$key] = [
            'label' => $def['label'],
            'avg'   => $row && $row['avg_score'] !== null ? round((float)$row['avg_score'], 2) : null,
            'count' => $row ? (int)$row['n'] : 0,
        ];
    }
    return $out;
}

/** Distribution of answers for a single non-rating question. */
function feedback_answer_distribution(string $category, string $questionKey): array {
    $stmt = db()->prepare(
        "SELECT a.answer_value, COUNT(*) AS n
         FROM feedback_answers a
         JOIN feedback_responses r ON r.id = a.response_id
         WHERE r.category = ? AND a.question_key = ? AND a.answer_value <> ''
         GROUP BY a.answer_value ORDER BY n DESC"
    );
    $stmt->execute([$category, $questionKey]);
    return $stmt->fetchAll();
}

/** True when the feedback forms are open for submissions (admin toggle). */
function feedback_is_open(): bool {
    return get_setting('feedback_open', '1') !== '0';
}

function set_feedback_open(bool $open): void {
    set_setting('feedback_open', $open ? '1' : '0');
}
