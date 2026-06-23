# Test Editing Bugs - Bugfix Design

## Overview

This design addresses two bugs and one feature addition in the VATANPARVAR driving test platform:

1. **Question edit with image upload causes question to disappear** — When an admin edits a question in `admin/savollar-form.php` and the form is submitted with `enctype="multipart/form-data"`, if the PHP `post_max_size` is exceeded (large image upload), all `$_POST` data is lost. The `vpy_post('bilet_id', 1)` call then defaults to `1`, silently reassigning the question to Bilet #1 and making it "disappear" from its original ticket.

2. **Users can re-select answers during test** — In `user/test.php`, the JavaScript click handler for `.q-answer` buttons does not check whether the question has already been answered. Users can click again (or use keyboard shortcuts F1-F4, 1-4) to change their selection, violating the "one answer per question" requirement.

3. **"Biletlar 50" practice mode missing** — The platform currently only supports "Biletlar 20" (20 questions per bilet, 25 minutes) and a "tez test" (quick test). Users need a "Biletlar 50" practice mode that groups ALL active questions into virtual bilets of 50 (ceil(1200/50) = 24 bilets) with a 60-minute timer. This mode is practice-only (mashq) — results are not saved to `natijalar` and do not affect user statistics.

The fix strategy is minimal and targeted: add server-side validation for Bug 1 (detect corrupted POST data), add client-side early-return guards for Bug 2 (prevent re-selection once answered), and implement the "Biletlar 50" feature in `user/testlar.php` and `user/test.php` with a new query function in `includes/functions.php`.

## Glossary

- **Bug_Condition (C)**: The condition that triggers the bug — for Bug 1: edit form submission with corrupted/empty POST data; for Bug 2: click/key event on an already-answered question; for Feature 3: user requests a "Biletlar 50" session which doesn't exist yet
- **Property (P)**: The desired behavior — for Bug 1: question preserves its `bilet_id` on edit; for Bug 2: answer selection is final and cannot be changed; for Feature 3: system loads 50 questions with 60-min timer and skips stats saving
- **Preservation**: Existing behavior that must remain unchanged — normal question editing, new question creation, first-time answer selection, navigation, scoring, existing "Biletlar 20" mode
- **`vpy_post()`**: Helper function in `includes/functions.php` that reads `$_POST` values with a default fallback
- **`bilet_id`**: The ticket (bilet) number a question belongs to (1-65), determining which test ticket the question appears in
- **`answers` object**: JavaScript object in `user/test.php` that stores `{question_id: selected_letter}` mappings during a test session
- **`vpy_test_questions_bilet50()`**: New function to be added in `includes/functions.php` that fetches all active questions ordered by id and returns a slice of 50 for a given virtual bilet number
- **`natijalar`**: JSON data file storing test results; "Biletlar 50" results are NOT saved here
- **`totalDuration`**: JavaScript variable in `user/test.php` controlling test timer; currently hardcoded to 1500 (25 min)

## Bug Details

### Bug 1: Question Disappears on Edit with Image Upload

The bug manifests when an admin edits an existing question and submits the form with a file upload that causes `$_POST` to become empty (typically when `post_max_size` is exceeded). The `vpy_post('bilet_id', 1)` function returns the default value `1`, and the UPDATE query silently reassigns the question to Bilet #1.

**Formal Specification:**
```
FUNCTION isBugCondition_EditQuestion(input)
  INPUT: input of type QuestionEditRequest
  OUTPUT: boolean
  
  RETURN input.is_edit = true
         AND input.form_enctype = 'multipart/form-data'
         AND (input.post_data_corrupted = true OR input.bilet_id_missing_from_post = true)
         AND question.original_bilet_id != vpy_post('bilet_id', 1)
END FUNCTION
```

### Bug 2: User Can Re-select Answers

The bug manifests when a user clicks (or uses keyboard shortcuts) on an answer button for a question that has already been answered. The click handler unconditionally processes the selection without checking `answers[qid]`.

**Formal Specification:**
```
FUNCTION isBugCondition_AnswerReselect(input)
  INPUT: input of type AnswerEvent (click or keyboard)
  OUTPUT: boolean
  
  RETURN input.target_question_id IN answers
         AND answers[input.target_question_id] IS NOT UNDEFINED
END FUNCTION
```

### Feature 3: "Biletlar 50" Practice Mode Missing

The platform currently only supports "Biletlar 20" (20 questions per bilet from `bilet_id` assignment in DB, 25 minutes) and a "tez test" (20 random questions). Users need a practice mode with larger question sets — "Biletlar 50" — that groups ALL active questions sequentially into virtual bilets of 50, provides a 60-minute timer, and does not persist results.

**Formal Specification:**
```
FUNCTION isBugCondition_Biletlar50(input)
  INPUT: input of type TestSessionRequest
  OUTPUT: boolean
  
  RETURN input.type = 'bilet50'
         AND input.bilet_number >= 1 AND input.bilet_number <= 24
         AND biletlar50_mode_not_implemented = true
END FUNCTION
```

### Examples

**Bug 1 Examples:**
- Admin edits question #1372 (Bilet #5, Tartib #3), uploads a 15MB image exceeding `post_max_size` → `$_POST` is empty → `bilet_id` defaults to `1` → question moves to Bilet #1 and "disappears" from Bilet #5
- Admin edits question #1428 (Bilet #12, Tartib #7), uploads a 2MB image within limits → POST data is intact → `bilet_id` correctly stays at `12` → no bug (normal operation)
- Admin edits question #1429 (Bilet #3, Tartib #1) without uploading any image → POST data is intact → `bilet_id` correctly stays at `3` → no bug (normal operation)

**Bug 2 Examples:**
- User answers question #5 with "A" (correct), then clicks "B" → answer changes to "B" (wrong) → BUG: should be blocked
- User answers question #5 with "A" (wrong), then presses F2 (for "B") → answer changes to "B" → BUG: should be blocked
- User clicks "C" for question #10 that has no prior answer → "C" is recorded → correct behavior (first selection)

**Feature 3 Examples:**
- User navigates to `/user/test.php?type=bilet50&bilet=1` → system loads questions 1-50 (first 50 by id), timer set to 60 minutes → correct behavior
- User navigates to `/user/test.php?type=bilet50&bilet=24` → system loads questions 1151-1200 (last group), timer set to 60 minutes → correct behavior
- User navigates to `/user/test.php?type=bilet50&bilet=12` → system loads questions 551-600, timer set to 60 minutes → correct behavior
- User completes a "Biletlar 50" test → system shows result page with score but does NOT call `vpy_upsert('natijalar', ...)` and does NOT update `users.tests_taken` or `users.best_score` → correct behavior
- User visits testlar.php → "tez test" button is replaced with link to "Biletlar 50" section; a new grid of 24 bilet cards appears below the "Biletlar 20" grid → correct behavior
- User navigates to `/user/test.php?type=bilet50&bilet=25` → invalid bilet (only 24 exist) → system redirects with error → correct behavior

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Admin editing a question WITHOUT an image upload must continue to save all fields correctly (bilet_id, tartib, savol, variants, togri, etc.)
- Admin editing a question WITH a valid image upload (within size limits) and correct POST data must continue to work normally
- Admin creating a new question (INSERT path) with or without image must continue to work normally
- First-time answer selection (click or keyboard) for an unanswered question must continue to work: highlight the selection, show correct/wrong feedback, record the answer
- Navigation between questions (Next/Previous buttons, question grid, arrow keys, F6/F7) must remain unchanged
- Timer countdown and auto-submit on timeout must remain unchanged
- Test submission and score calculation must remain unchanged
- Question grid color coding (green for correct, red for wrong) must remain unchanged
- The finish confirmation dialog must remain unchanged
- Existing "Biletlar 20" bilet grid on testlar.php must remain fully functional (62 bilets displayed, linking to `/user/test.php?bilet=N`, 25-minute timer, results saved to natijalar)
- Standard bilet tests (`type=bilet`, `?bilet=N`) must continue to save results to `natijalar` and update user statistics
- The quick test (`type=quick`) functionality is being replaced by "Biletlar 50" on the testlar.php page, but the underlying quick test route (`/user/test.php?type=quick`) still works if accessed directly
- Admin panel must have no knowledge of "Biletlar 50" — it only manages questions by their real `bilet_id` (1-62)

**Scope:**
- For Bug 1: All edit submissions where POST data is properly received (not corrupted) should be completely unaffected by the fix
- For Bug 2: All interactions that are NOT answer re-selection (first clicks, navigation, timer, submission) should be completely unaffected
- For Feature 3: All existing "Biletlar 20" test flows, admin panel operations, and result-saving logic for standard/bilet tests should be completely unaffected by the addition of the new mode

## Hypothesized Root Cause

### Bug 1: Missing POST Data Validation

Based on the code analysis, the root causes are:

1. **No `post_max_size` overflow detection**: When a file upload exceeds PHP's `post_max_size`, both `$_POST` and `$_FILES` become empty arrays. The code does not detect this condition and proceeds with default values.

2. **Silent default fallback in `vpy_post()`**: The call `vpy_post('bilet_id', 1)` returns `1` when `$_POST['bilet_id']` is not set, which silently assigns the question to Bilet #1 instead of preserving the original value or showing an error.

3. **No validation of critical fields before UPDATE**: The code builds the `$data` array and immediately constructs the UPDATE query without verifying that essential fields (especially `bilet_id`) match expected values or are explicitly provided by the user.

### Bug 2: Missing "Already Answered" Guard

Based on the JavaScript code analysis:

1. **No early-return check in click handler**: The `.q-answer` click event listener does not check whether `answers[qid]` already exists before processing the click.

2. **Keyboard handlers delegate to `.click()`**: The F1-F4 and 1-4 key handlers call `btn.click()`, which triggers the same unguarded click handler. Fixing the click handler will automatically fix keyboard shortcuts.

3. **No CSS pointer-events disable**: After answering, the answer buttons are not disabled or made unclickable via CSS or DOM attributes.

### Feature 3: "Biletlar 50" Mode Not Implemented

Based on the codebase analysis:

1. **No `type=bilet50` handling in `test.php`**: The file currently only handles `type=quick` (random 20 questions) and `?bilet=N` (bilet_id-based lookup). There is no code path for a virtual bilet grouping of 50 questions.

2. **No `vpy_test_questions_bilet50()` function**: The `includes/functions.php` has `vpy_test_questions()` (random or by bilet_id) and `vpy_test_questions_by_bilet()` (by DB bilet_id field), but no function that fetches ALL questions ordered by id and slices them into groups of 50.

3. **Timer is hardcoded**: In `test.php`, `var totalDuration = 1500;` (25 minutes) regardless of test type. There is no conditional logic to set a different duration for different modes.

4. **Results always saved**: The POST handler in `test.php` unconditionally calls `vpy_upsert('natijalar', $row)` and updates `$u['tests_taken']` / `$u['best_score']`. There is no check for a practice-only mode that should skip saving.

5. **testlar.php has no "Biletlar 50" section**: The page only renders a grid for "Biletlar 20" (bilet_id 1-62) and a "tez test" button linking to `/user/test.php?type=quick`. No "Biletlar 50" section or cards exist.

6. **Bilet validation range is wrong**: In `test.php`, the validation `if ($bilet_id > 0 && ($bilet_id < 1 || $bilet_id > 65))` applies to all bilet requests but doesn't account for the new `type=bilet50` mode which uses a different range (1-24).

## Correctness Properties

Property 1: Bug Condition - Question Preserves bilet_id on Edit

_For any_ question edit request where the POST data is corrupted or missing (isBugCondition_EditQuestion returns true), the fixed `admin/savollar-form.php` SHALL detect the corrupted state, abort the UPDATE operation, and display an error message to the admin, preserving the question's original `bilet_id` in the database.

**Validates: Requirements 2.1, 2.2**

Property 2: Bug Condition - Answer Re-selection is Blocked

_For any_ answer event (click or keyboard) where the target question has already been answered (isBugCondition_AnswerReselect returns true), the fixed click handler SHALL ignore the event and keep the original answer selection unchanged in the `answers` object.

**Validates: Requirements 2.3, 2.4**

Property 3: Bug Condition - Biletlar 50 Loads Correct Questions with Correct Timer

_For any_ test session request where `type=bilet50` and `bilet=N` (N in 1-24), the fixed system SHALL load exactly 50 questions (or fewer for the last bilet if total questions < N*50) computed as ALL active questions ordered by id with offset `(N-1)*50`, set the timer to 3600 seconds (60 minutes), and upon completion SHALL NOT save results to `natijalar` and SHALL NOT update user statistics.

**Validates: Requirements 2.5, 2.6, 2.7, 2.8**

Property 4: Preservation - Normal Question Editing

_For any_ question edit request where POST data is properly received (isBugCondition_EditQuestion returns false), the fixed code SHALL produce exactly the same result as the original code, preserving all existing edit and create functionality.

**Validates: Requirements 3.1, 3.2**

Property 5: Preservation - First Answer Selection and Navigation

_For any_ user interaction that is NOT an answer re-selection (first-time answer clicks, navigation, timer, submission), the fixed code SHALL produce exactly the same behavior as the original code, preserving all existing test-taking functionality.

**Validates: Requirements 3.3, 3.4, 3.5**

Property 6: Preservation - Existing Biletlar 20 and Statistics Unchanged

_For any_ test session that is NOT of type `bilet50` (standard bilet tests, quick tests), the fixed code SHALL continue to save results to `natijalar`, update `users.tests_taken` and `users.best_score`, and display the "Biletlar 20" grid with 62 bilets unchanged on testlar.php.

**Validates: Requirements 3.6, 3.7, 3.8, 3.9**

## Fix Implementation

### Changes Required

#### Bug 1 Fix

**File**: `admin/savollar-form.php`

**Function**: POST handling block (around line 29-67)

**Specific Changes**:

1. **Detect corrupted POST data**: After `if (vpy_is_post() && vpy_csrf_check(...))`, add a check for empty/corrupted POST data. If `$_SERVER['CONTENT_LENGTH'] > 0` but `$_POST` is empty, this indicates `post_max_size` was exceeded.

   ```php
   // Detect post_max_size overflow
   if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
       vpy_flash_set('error', 'Fayl hajmi juda katta. Iltimos, kichikroq rasm yuklang.');
       vpy_redirect('/admin/savollar-form.php?id=' . $id);
   }
   ```

2. **Preserve original bilet_id on edit**: When editing, if the submitted `bilet_id` equals the default `1` but the original question has a different `bilet_id`, use the original value as a safety measure. Better: validate that the form was properly submitted.

   ```php
   // For edits, validate that bilet_id was explicitly submitted
   if ($is_edit && !isset($_POST['bilet_id'])) {
       $data['bilet_id'] = (int)$q['bilet_id']; // Preserve original
   }
   ```

3. **Add upload size validation**: Before processing the file upload, check if the file size is within acceptable limits.

4. **Show error on CSRF failure with multipart**: Ensure that CSRF check failure also redirects with an error message rather than silently doing nothing (already handled by existing logic, but worth confirming).

#### Bug 2 Fix

**File**: `user/test.php`

**Function**: JavaScript click handler for `.q-answer` buttons (around line 195-220 in the `<script>` section)

**Specific Changes**:

1. **Add early-return guard in click handler**: At the start of the `.q-answer` click event listener, check if `answers[qid]` already exists:

   ```javascript
   a.addEventListener('click', function(){
       var qid = q.getAttribute('data-q-id');
       
       // Agar savol allaqachon javob berilgan bo'lsa, qayta tanlashga ruxsat bermaslik
       if (answers[qid] !== undefined) return;
       
       // ... rest of existing handler
   });
   ```

2. **Add visual indication of locked state** (optional enhancement): After answering, add a CSS class to disable hover effects on the answer buttons:

   ```javascript
   // After recording the answer, disable further interaction visually
   q.classList.add('answered');
   ```

   With CSS:
   ```css
   .q-card.answered .q-answer { cursor: default; pointer-events: none; }
   .q-card.answered .q-answer:hover { transform: none; border-color: var(--border); }
   ```

3. **No change needed for keyboard handlers**: Since F1-F4 and 1-4 handlers call `btn.click()`, the early-return guard in the click handler automatically blocks keyboard re-selection as well.

#### Feature 3 Fix: "Biletlar 50" Practice Mode

**File**: `includes/functions.php`

**New Function**: `vpy_test_questions_bilet50($bilet_num)`

**Purpose**: Fetch all active questions ordered by id, then return the slice for virtual bilet N (50 questions per bilet).

**Implementation**:
```php
function vpy_test_questions_bilet50($bilet_num) {
    $pdo = vpy_pdo();
    if (!$pdo) return [];
    try {
        $st = $pdo->query("SELECT * FROM test_savollar WHERE holat='faol' ORDER BY id ASC");
        $all = $st->fetchAll();
        $offset = ((int)$bilet_num - 1) * 50;
        return array_slice($all, $offset, 50);
    } catch (Exception $e) {
        return [];
    }
}
```

---

**File**: `user/test.php`

**Changes Required**:

1. **Accept `type=bilet50` parameter**: Add handling for the new type in the question-loading logic:

   ```php
   if ($type === 'bilet50' && $bilet_id > 0) {
       // Validate bilet range (1-24)
       $total_questions = vpy_test_count();
       $max_bilet50 = (int)ceil($total_questions / 50);
       if ($bilet_id < 1 || $bilet_id > $max_bilet50) {
           vpy_redirect('/user/testlar.php?error=invalid_bilet50');
       }
       $questions = vpy_test_questions_bilet50($bilet_id);
   } elseif ($bilet_id > 0) {
       $questions = vpy_test_questions_by_bilet($bilet_id);
   } else {
       $questions = vpy_test_questions(20, null);
   }
   ```

2. **Set timer to 3600 seconds when type=bilet50**: Modify the JavaScript `totalDuration` variable:

   ```javascript
   var totalDuration = <?= $type === 'bilet50' ? 3600 : 1500 ?>;
   ```

3. **Skip saving results when type=bilet50**: In the POST handler, wrap the `vpy_upsert` and user stats update in a condition:

   ```php
   if ($session_type !== 'bilet50') {
       vpy_upsert('natijalar', $row);
       $u['tests_taken'] = ((int)($u['tests_taken'] ?? 0)) + 1;
       if ($correct > (int)($u['best_score'] ?? 0)) $u['best_score'] = $correct;
       vpy_upsert('users', $u);
       vpy_log('test_finish', "Test yakunlandi: $correct/" . count($answers), ['user_id' => $u['id'], 'type' => $row['type']]);
       vpy_redirect('/user/test-result.php?id=' . $row['id']);
   } else {
       // Biletlar 50 - show result inline without persisting
       // Redirect to a result display that doesn't require natijalar lookup
       $_SESSION['bilet50_result'] = [
           'score' => $correct,
           'total' => count($answers),
           'wrong' => count($answers) - $correct,
           'duration' => $duration,
           'bilet_num' => $session_bilet
       ];
       vpy_redirect('/user/test-result.php?type=bilet50');
   }
   ```

4. **Update bilet_id validation**: Remove or adjust the hardcoded `$bilet_id > 65` check to allow bilet50 bilet numbers:

   ```php
   if ($type !== 'bilet50' && $bilet_id > 0 && ($bilet_id < 1 || $bilet_id > 65)) {
       vpy_redirect('/user/test.php?error=invalid_ticket');
   }
   ```

5. **Add hidden field for test_type**: Ensure the form POST includes `test_type=bilet50` so the server knows to skip saving.

---

**File**: `user/testlar.php`

**Changes Required**:

1. **Replace "tez test" button**: Change the topbar action button from linking to `/user/test.php?type=quick` to an anchor that scrolls to the "Biletlar 50" section:

   ```php
   vpy_panel_topbar(t('tickets_title'), t('tickets_subtitle'),
       '<a href="#biletlar50" class="btn btn-primary">...</a>'
   );
   ```

2. **Add "Biletlar 50" section below "Biletlar 20" grid**: After the existing `.card` div for "Biletlar 20", add a new card section:

   ```php
   <div class="card" id="biletlar50">
       <div class="card-head">
           <h2>Biletlar 50 · Mashq rejimi</h2>
           <span class="chip chip-warning">60 daqiqa · 50 savol</span>
       </div>
       <p style="color:var(--muted);margin-bottom:18px;font-size:0.9rem;">
           Barcha savollar 50 talik guruhlarga bo'lingan. Natijalar saqlanmaydi — faqat mashq uchun.
       </p>
       <div class="bilet-grid">
           <?php
           $total_q = vpy_test_count();
           $total_bilet50 = (int)ceil($total_q / 50);
           for ($i = 1; $i <= $total_bilet50; $i++):
           ?>
               <a href="/user/test.php?type=bilet50&bilet=<?= $i ?>" class="bilet-card">
                   <div>
                       <div class="bilet-label">Bilet 50</div>
                       <div class="bilet-num"><?= sprintf('%02d', $i) ?></div>
                   </div>
                   <div class="bilet-meta">
                       <span class="bilet-count">50 <?= e(t('ticket_count')) ?></span>
                   </div>
               </a>
           <?php endfor; ?>
       </div>
   </div>
   ```

3. **No "done" tracking**: Since results are not saved for "Biletlar 50", the bilet cards never get the `.done` class or score badge. They remain in default style at all times.

---

**File**: `user/test-result.php` (minor change)

**Changes Required**:

1. **Handle `type=bilet50` results**: When `$_GET['type'] === 'bilet50'`, read the result from `$_SESSION['bilet50_result']` instead of looking up from `natijalar`. Display the same result UI (score, correct/wrong count, duration) but without a "saved" indicator and without linking to historical results.

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the bugs on unfixed code, then verify the fixes work correctly and preserve existing behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the bugs BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan for Bug 1**: Simulate a form submission where `$_POST` is empty (mimicking `post_max_size` exceeded) and verify that the question's `bilet_id` changes to `1`. Run on UNFIXED code to confirm the bug.

**Test Cases**:
1. **POST overflow simulation**: Submit edit form with `$_POST` manually emptied while `CONTENT_LENGTH > 0` → observe bilet_id changes to 1 (will fail on unfixed code in the sense that it silently corrupts data)
2. **Normal edit with image**: Submit edit form with valid image and correct POST data → observe bilet_id preserved (should work on unfixed code)
3. **Edit without image**: Submit edit form without file → observe bilet_id preserved (should work on unfixed code)

**Test Plan for Bug 2**: Click on an answer for a question, then click again on a different answer. Run on UNFIXED code to confirm re-selection is allowed.

**Test Cases**:
1. **Double-click test**: Click "A" then click "B" on same question → observe answer changes (will succeed as a bug on unfixed code)
2. **Keyboard re-selection**: Click "A" then press F2 → observe answer changes (will succeed as a bug on unfixed code)
3. **Number key re-selection**: Click "A" then press "2" → observe answer changes (will succeed as a bug on unfixed code)

**Test Plan for Feature 3**: Access `/user/test.php?type=bilet50&bilet=N` on UNFIXED code and confirm that the route either doesn't exist or falls through to the default quick test behavior (20 random questions, 25-min timer, results saved).

**Test Cases**:
1. **bilet50 route test**: Navigate to `/user/test.php?type=bilet50&bilet=1` → observe that 20 random questions load (not 50) and timer is 25 min (not 60 min) (demonstrates missing feature on unfixed code)
2. **testlar.php missing section**: Check testlar.php for "Biletlar 50" section → observe it doesn't exist (demonstrates missing feature)
3. **Results always saved**: Complete test via `type=bilet50` route → observe result is saved to natijalar (demonstrates missing "no-save" logic)

**Expected Counterexamples**:
- Bug 1: `bilet_id` silently changes to `1` when POST data is lost
- Bug 2: `answers[qid]` gets overwritten on second click without any guard
- Feature 3: No `bilet50` handling exists — falls through to default behavior

### Fix Checking

**Goal**: Verify that for all inputs where the bug conditions hold, the fixed functions produce the expected behavior.

**Pseudocode:**
```
// Bug 1: Fix Checking
FOR ALL input WHERE isBugCondition_EditQuestion(input) DO
  result := savollarForm_fixed(input)
  ASSERT result.question_bilet_id = input.original_bilet_id
  ASSERT result.error_shown = true
  ASSERT result.update_aborted = true
END FOR

// Bug 2: Fix Checking
FOR ALL input WHERE isBugCondition_AnswerReselect(input) DO
  result := handleAnswerClick_fixed(input)
  ASSERT answers[input.question_id] = input.original_answer
  ASSERT result.event_ignored = true
END FOR

// Feature 3: Fix Checking
FOR ALL input WHERE isBugCondition_Biletlar50(input) DO
  result := startTestSession_fixed(input)
  ASSERT result.question_count = 50 OR (input.bilet_num = max_bilet AND result.question_count = total_questions MOD 50)
  ASSERT result.timer_seconds = 3600
  ASSERT result.saves_to_natijalar = false
  ASSERT result.updates_user_stats = false
  ASSERT result.questions = allQuestions[(input.bilet_num - 1) * 50 : input.bilet_num * 50]
END FOR
```

### Preservation Checking

**Goal**: Verify that for all inputs where the bug conditions do NOT hold, the fixed functions produce the same result as the original functions.

**Pseudocode:**
```
// Bug 1: Preservation
FOR ALL input WHERE NOT isBugCondition_EditQuestion(input) DO
  ASSERT savollarForm_original(input) = savollarForm_fixed(input)
END FOR

// Bug 2: Preservation
FOR ALL input WHERE NOT isBugCondition_AnswerReselect(input) DO
  ASSERT handleAnswerClick_original(input) = handleAnswerClick_fixed(input)
END FOR

// Feature 3: Preservation
FOR ALL input WHERE NOT isBugCondition_Biletlar50(input) DO
  // Standard bilet tests still save to natijalar
  IF input.type = 'bilet' OR input.type = 'quick' THEN
    ASSERT testSession_original(input).saves_to_natijalar = testSession_fixed(input).saves_to_natijalar
    ASSERT testSession_original(input).updates_user_stats = testSession_fixed(input).updates_user_stats
  END IF
  // testlar.php still shows Biletlar 20 grid correctly
  ASSERT biletlar20_grid_unchanged()
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for normal operations (edits without overflow, first-time answer selections, standard bilet tests), then write property-based tests capturing that behavior.

**Test Cases**:
1. **Normal edit preservation**: Verify editing questions with valid POST data continues to work correctly after the fix
2. **New question preservation**: Verify creating new questions with/without images continues to work
3. **First answer selection preservation**: Verify that first-time clicks on unanswered questions continue to record, highlight, and show feedback
4. **Navigation preservation**: Verify Next/Previous, question grid, arrow keys, F6/F7 continue working
5. **Timer and submission preservation**: Verify countdown and auto-submit are unaffected
6. **Biletlar 20 result saving preservation**: Verify that completing a standard bilet test still saves to natijalar and updates user statistics
7. **Biletlar 20 grid display preservation**: Verify testlar.php still shows 62 bilet cards with correct links, done status, and scores
8. **Admin panel preservation**: Verify no changes visible in admin — bilet_id management unchanged

### Unit Tests

- Test `post_max_size` overflow detection logic (empty POST with non-zero CONTENT_LENGTH)
- Test that `bilet_id` is preserved from original record when POST is corrupted
- Test that error flash message is set when overflow is detected
- Test that the click handler returns early when `answers[qid]` exists
- Test that first click on unanswered question still records the answer
- Test edge cases: question with only 2 variants (A, B), question at boundaries (first/last)
- Test `vpy_test_questions_bilet50()` returns correct 50-question slice for bilet 1 (questions 1-50 by id)
- Test `vpy_test_questions_bilet50()` returns correct slice for bilet 24 (last group, possibly fewer than 50)
- Test `vpy_test_questions_bilet50()` returns empty array for bilet 0 or bilet > max
- Test timer is set to 3600 when `type=bilet50` and 1500 for all other types
- Test that POST handler skips `vpy_upsert('natijalar', ...)` when `test_type=bilet50`
- Test that POST handler skips user stats update when `test_type=bilet50`
- Test bilet50 validation: bilet=25 should redirect with error when only 24 bilets exist

### Property-Based Tests

- Generate random question edit scenarios with various `bilet_id` values and verify preservation when POST is valid
- Generate random sequences of answer clicks and verify only the first click per question is recorded
- Generate random navigation patterns interleaved with answer selections and verify state consistency
- Generate random keyboard inputs (F1-F4, 1-4, arrows, F6, F7) and verify only first answer per question is accepted
- Generate random bilet numbers (1-24) and verify `vpy_test_questions_bilet50()` always returns questions from the correct offset range and never overlaps with adjacent bilets
- Generate random total question counts and verify `ceil(total/50)` correctly determines max bilet number
- Generate random test completions with type=bilet50 and verify natijalar JSON file is never modified

### Integration Tests

- Full admin workflow: create question → edit with image → verify bilet_id preserved
- Full test workflow: start test → answer all questions once → verify no changes possible → submit → verify score
- Mixed interaction: answer some via click, some via keyboard, try to re-answer → verify final state matches first selections only
- Timer expiry: answer some questions, let timer expire → verify submitted answers are all first selections
- Full "Biletlar 50" workflow: visit testlar.php → see "Biletlar 50" section with 24 cards → click bilet 1 → verify 50 questions loaded → verify timer shows 60:00 → answer all → submit → verify result shown → verify natijalar NOT updated → verify user stats NOT updated
- "Biletlar 50" and "Biletlar 20" coexistence: complete a Biletlar 20 test (result saved), then complete a Biletlar 50 test (result not saved) → verify only the Biletlar 20 result appears in natijalar
- testlar.php layout: verify both "Biletlar 20" grid (62 cards) and "Biletlar 50" grid (24 cards) render correctly on the same page
- "Biletlar 50" question ordering: verify bilet 1 has questions with lowest ids, bilet 2 has next 50, etc. — no randomization, strict id ordering
