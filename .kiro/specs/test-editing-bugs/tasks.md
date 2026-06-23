# Implementation Plan

- [ ] 1. Write bug condition exploration tests (BEFORE implementing any fix)
  - **Property 1: Bug Condition** - Question Edit POST Overflow, Answer Re-selection, and Missing Biletlar 50
  - **CRITICAL**: These tests MUST FAIL on unfixed code — failure confirms the bugs exist
  - **DO NOT attempt to fix the tests or the code when they fail**
  - **NOTE**: These tests encode the expected behavior — they will validate the fixes when they pass after implementation
  - **GOAL**: Surface counterexamples that demonstrate each bug exists
  - **Scoped PBT Approach**: For deterministic bugs, scope the property to concrete failing cases
  - **Bug 1 Test** (admin/savollar-form.php): Simulate a form submission where `$_SERVER['CONTENT_LENGTH'] > 0` but `$_POST` is empty (mimicking `post_max_size` exceeded). Verify that the question's `bilet_id` changes to `1` on unfixed code (confirms silent data corruption). The test asserts expected behavior: on corrupted POST, the system should detect overflow and abort the UPDATE, preserving original `bilet_id`.
  - **Bug 2 Test** (user/test.php JS): Simulate clicking answer "A" for a question, then clicking "B" for the same question. On unfixed code, the `answers[qid]` object will be overwritten with "B" — confirms re-selection is allowed. The test asserts expected behavior: `answers[qid]` should remain "A" after the second click.
  - **Feature 3 Test** (user/test.php): Navigate to `/user/test.php?type=bilet50&bilet=1` on unfixed code. Observe that either 20 random questions load (not 50) or the page errors. Verify timer is 1500s (not 3600s). Verify results are saved to natijalar (not skipped). Confirms the `bilet50` mode doesn't exist.
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests FAIL (this is correct — it proves the bugs exist)
  - Document counterexamples found:
    - Bug 1: `bilet_id` silently changes to `1` when POST data is lost during image upload
    - Bug 2: `answers[qid]` gets overwritten on second click without any guard
    - Feature 3: No `type=bilet50` handling — falls through to default quick test behavior (20 questions, 25-min timer, results saved)
  - Mark task complete when tests are written, run, and failures are documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6_

- [ ] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Normal Edit, First Answer Selection, and Biletlar 20 Behavior
  - **IMPORTANT**: Follow observation-first methodology
  - **Observe on UNFIXED code**:
    - Normal question edit (valid POST, no overflow): `bilet_id` is preserved correctly
    - Normal question edit without image: all fields saved correctly
    - New question creation with/without image: INSERT works with correct `bilet_id`
    - First-time answer click on unanswered question: answer is recorded, highlighted, correct/wrong feedback shown
    - Navigation (Next/Prev, grid, arrows, F6/F7): works freely between questions
    - Timer countdown: ticks correctly, auto-submits at 0
    - Test submission: score calculated correctly, saved to natijalar, user stats updated
    - Standard bilet test (`?bilet=N`): loads by bilet_id, 25-min timer, results saved
    - testlar.php: shows 62 bilet cards with done/score status
  - Write property-based tests capturing observed behavior:
    - For all question edit requests with valid POST data (`$_POST` non-empty, `bilet_id` present): result `bilet_id` equals submitted `bilet_id` (Preservation Req 3.1, 3.2)
    - For all first-time answer clicks (where `answers[qid]` is undefined): answer is recorded and UI shows feedback (Preservation Req 3.3)
    - For all navigation events: questions display correctly, selected answers remain highlighted (Preservation Req 3.4)
    - For all standard test submissions (type != bilet50): result is saved to natijalar and user stats updated (Preservation Req 3.5, 3.8)
    - For all visits to testlar.php: Biletlar 20 grid displays 62 bilet cards with correct links and status (Preservation Req 3.6)
  - Verify tests PASS on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9_

- [ ] 3. Fix Bug 1: Question disappears on edit with image upload

  - [ ] 3.1 Add POST overflow detection in `admin/savollar-form.php`
    - Add check AFTER `if (vpy_is_post() && vpy_csrf_check(...))` and BEFORE processing form data
    - Detect `post_max_size` overflow: if `empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0`
    - When detected: set flash error message "Fayl hajmi juda katta. Iltimos, kichikroq rasm yuklang (max 5MB)."
    - Redirect back to `/admin/savollar-form.php?id={$id}` to abort the update
    - This prevents any UPDATE from executing with corrupted/empty POST data
    - _Bug_Condition: isBugCondition_EditQuestion(input) where is_edit=true AND post_data_corrupted=true_
    - _Expected_Behavior: Abort UPDATE, show error, preserve original bilet_id in database_
    - _Preservation: Normal edits with valid POST must continue to work unchanged_
    - _Requirements: 2.1, 2.2, 3.1, 3.2_

  - [ ] 3.2 Add fallback bilet_id preservation for edit mode
    - In the edit path (`$is_edit` block), after building `$data` array
    - Add validation: if `!isset($_POST['bilet_id'])` when editing, use original `$q['bilet_id']` instead of the default `1`
    - This is a safety net in case POST data is partially corrupted (some fields missing but not all)
    - Code: `if ($is_edit && !isset($_POST['bilet_id'])) { $data['bilet_id'] = (int)$q['bilet_id']; }`
    - _Bug_Condition: bilet_id missing from POST during edit_
    - _Expected_Behavior: Preserve original bilet_id from database record_
    - _Requirements: 2.1, 2.2_

  - [ ] 3.3 Verify bug condition exploration test now passes for Bug 1
    - **Property 1: Expected Behavior** - Question Preserves bilet_id on Edit
    - **IMPORTANT**: Re-run the SAME test from task 1 (Bug 1 portion) — do NOT write a new test
    - The test from task 1 encodes the expected behavior: corrupted POST should be detected and UPDATE aborted
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed — overflow detected, bilet_id preserved)
    - _Requirements: 2.1, 2.2_

- [ ] 4. Fix Bug 2: User can re-select answers during test

  - [ ] 4.1 Add early-return guard in JavaScript click handler in `user/test.php`
    - In the `.q-answer` click event listener (inside the `questions.forEach` loop)
    - Add check at the START of the handler: `var qid = q.getAttribute('data-q-id'); if (answers[qid] !== undefined) return;`
    - This prevents any answer re-selection — both click and keyboard (since F1-F4 and 1-4 handlers call `btn.click()`)
    - _Bug_Condition: isBugCondition_AnswerReselect(input) where answers[qid] already exists_
    - _Expected_Behavior: Handler returns immediately, answers[qid] unchanged_
    - _Preservation: First-time answer selection still works normally_
    - _Requirements: 2.3, 2.4, 3.3_

  - [ ] 4.2 Add CSS/visual lock after answering in `user/test.php`
    - After recording the answer and showing correct/wrong feedback, add class to the question card: `q.classList.add('answered');`
    - Add CSS rule: `.q-card.answered .q-answer { cursor: default; pointer-events: none; }` and `.q-card.answered .q-answer:hover { transform: none; border-color: var(--border); background: rgba(255,253,249,0.6); }`
    - This provides visual feedback that the question is locked (no hover effects, no pointer cursor)
    - _Expected_Behavior: Answered questions visually indicate they cannot be changed_
    - _Preservation: Navigation, timer, grid still work — only answer buttons are disabled_
    - _Requirements: 2.3, 2.4_

  - [ ] 4.3 Verify bug condition exploration test now passes for Bug 2
    - **Property 1: Expected Behavior** - Answer Re-selection is Blocked
    - **IMPORTANT**: Re-run the SAME test from task 1 (Bug 2 portion) — do NOT write a new test
    - The test from task 1 encodes the expected behavior: second click should be ignored
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed — re-selection blocked)
    - _Requirements: 2.3, 2.4_

- [ ] 5. Implement Feature 3: "Biletlar 50" practice mode

  - [ ] 5.1 Add `vpy_test_questions_bilet50()` function in `includes/functions.php`
    - Add new function after `vpy_test_questions_by_bilet()` function
    - Function signature: `function vpy_test_questions_bilet50($bilet_num)`
    - Implementation: query all active questions (`SELECT * FROM test_savollar WHERE holat='faol' ORDER BY id ASC`), then `array_slice($all, ($bilet_num - 1) * 50, 50)`
    - Returns array of 50 questions (or fewer for last bilet if total < bilet_num * 50)
    - Returns empty array for invalid bilet_num (< 1 or > max)
    - _Bug_Condition: isBugCondition_Biletlar50(input) where type='bilet50'_
    - _Expected_Behavior: Returns 50 sequential questions by id for given virtual bilet number_
    - _Requirements: 2.5, 2.6_

  - [ ] 5.2 Update `user/test.php` to handle `type=bilet50` question loading
    - Add new condition BEFORE the existing `if ($bilet_id > 0)` block
    - When `$type === 'bilet50' && $bilet_id > 0`: calculate `$max_bilet50 = (int)ceil(vpy_test_count() / 50)`, validate `$bilet_id` is in range 1-$max_bilet50, then call `$questions = vpy_test_questions_bilet50($bilet_id)`
    - If bilet_id out of range: `vpy_redirect('/user/testlar.php?error=invalid_bilet50')`
    - Update the existing bilet validation at line ~10: add condition `$type !== 'bilet50'` so it doesn't interfere with bilet50 range (1-24 vs 1-65)
    - _Requirements: 2.5, 2.6_

  - [ ] 5.3 Update `user/test.php` timer to use 3600s for bilet50 mode
    - Change the JavaScript `totalDuration` variable from hardcoded `1500` to: `var totalDuration = <?= $type === 'bilet50' ? 3600 : 1500 ?>;`
    - This sets timer to 60 minutes for Biletlar 50 and keeps 25 minutes for all other modes
    - _Expected_Behavior: Timer shows 60:00 for bilet50, 25:00 for all others_
    - _Preservation: Standard bilet and quick tests remain at 25 minutes_
    - _Requirements: 2.6, 3.6_

  - [ ] 5.4 Update `user/test.php` POST handler to skip saving for bilet50
    - In the POST handler (after score calculation), wrap `vpy_upsert('natijalar', $row)`, user stats update (`$u['tests_taken']`, `$u['best_score']`), `vpy_upsert('users', $u)`, and `vpy_log(...)` inside: `if ($session_type !== 'bilet50') { ... }`
    - For bilet50 mode: store result in `$_SESSION['bilet50_result']` with keys: score, total, wrong, duration, bilet_num
    - Redirect to `/user/test-result.php?type=bilet50` instead of `?id=...`
    - _Bug_Condition: type='bilet50' — results must NOT be persisted_
    - _Expected_Behavior: No write to natijalar, no user stats update, session-based result display_
    - _Preservation: All non-bilet50 tests continue to save results and update stats_
    - _Requirements: 2.7, 3.5, 3.8_

  - [ ] 5.5 Update `user/test-result.php` to handle bilet50 session-based results
    - Add check at the top: if `vpy_get('type') === 'bilet50'`, read result from `$_SESSION['bilet50_result']`
    - If session data missing, redirect to `/user/testlar.php`
    - Display same result UI (score circle, stats, time) but without "saved" indicator
    - Change "retry" button to link back to `/user/test.php?type=bilet50&bilet={bilet_num}`
    - Add note: "Mashq rejimi — natijalar saqlanmaydi" (Practice mode — results not saved)
    - Clear `$_SESSION['bilet50_result']` after reading to prevent stale data
    - _Requirements: 2.7_

  - [ ] 5.6 Update `user/testlar.php` to add Biletlar 50 section and replace tez test button
    - Replace the "tez test" button in `vpy_panel_topbar()` with an anchor to `#biletlar50` section: `<a href="#biletlar50" class="btn btn-primary">...Biletlar 50 · Mashq...</a>`
    - After the existing "Biletlar 20" `.card` div, add a new `.card` section with id `biletlar50`
    - Card header: "Biletlar 50 · Mashq rejimi" with chip "60 daqiqa · 50 savol"
    - Add description paragraph: "Barcha savollar 50 talik guruhlarga bo'lingan. Natijalar saqlanmaydi — faqat mashq uchun."
    - Calculate total bilet50 count: `$total_bilet50 = (int)ceil(vpy_test_count() / 50);` (should be ~24)
    - Render a `.bilet-grid` with cards for bilets 1 to $total_bilet50, each linking to `/user/test.php?type=bilet50&bilet={$i}`
    - Cards use bilet-card styling but never have `.done` class (no tracking for practice mode)
    - Each card shows "Bilet 50" label, bilet number, and "50 savol" count
    - _Preservation: Existing "Biletlar 20" grid with 62 bilets remains fully unchanged above_
    - _Requirements: 2.5, 2.8, 3.6, 3.9_

  - [ ] 5.7 Verify bug condition exploration test now passes for Feature 3
    - **Property 1: Expected Behavior** - Biletlar 50 Loads Correct Questions with Correct Timer
    - **IMPORTANT**: Re-run the SAME test from task 1 (Feature 3 portion) — do NOT write a new test
    - The test from task 1 encodes the expected behavior: 50 questions loaded, 60-min timer, no stats saved
    - Run bug condition exploration test from step 1
    - **EXPECTED OUTCOME**: Test PASSES (confirms feature is implemented correctly)
    - _Requirements: 2.5, 2.6, 2.7, 2.8_

  - [ ] 5.8 Verify preservation tests still pass
    - **Property 2: Preservation** - Normal Edit, First Answer Selection, and Biletlar 20 Behavior
    - **IMPORTANT**: Re-run the SAME tests from task 2 — do NOT write new tests
    - Run all preservation property tests from step 2
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after all fixes:
      - Normal question edits with valid POST still preserve bilet_id
      - New question creation still works
      - First-time answer selection still records and shows feedback
      - Navigation still works freely
      - Standard bilet tests still save to natijalar
      - testlar.php still shows Biletlar 20 grid correctly (62 bilets)
      - User statistics still updated for non-bilet50 tests
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9_

- [ ] 6. Checkpoint - Ensure all tests pass
  - Run full test suite to verify:
    - Bug 1 exploration test PASSES (overflow detected, UPDATE aborted)
    - Bug 2 exploration test PASSES (re-selection blocked)
    - Feature 3 exploration test PASSES (50 questions, 60-min timer, no stats)
    - All preservation tests PASS (no regressions in existing functionality)
  - Verify manually:
    - Admin can edit questions with/without images and bilet_id is always preserved
    - Users cannot change answers once selected (click and keyboard both blocked)
    - testlar.php shows both Biletlar 20 (62 bilets) and Biletlar 50 (24 bilets) sections
    - Biletlar 50 loads 50 questions with 60-min timer
    - Biletlar 50 results are shown but NOT saved to natijalar or user stats
    - Standard bilet tests (Biletlar 20) continue to save results normally
  - Ensure all tests pass, ask the user if questions arise.
