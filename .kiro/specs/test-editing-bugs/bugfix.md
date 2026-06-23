# Bugfix Requirements Document

## Introduction

This document addresses two bugs and one feature addition in the VATANPARVAR driving test platform:

1. **Question disappears from ticket on edit with image upload** — When an admin edits a test question in `admin/savollar-form.php` and uploads an image, the question loses its `bilet_id` association (gets set to an incorrect value), causing it to "disappear" from its assigned ticket view.

2. **Users can re-select answers during test** — In `user/test.php`, once a user selects an answer for a question, they can click again to change their selection. The requirement is that answer selection should be final — one click per question with no ability to change.

3. **"Biletlar 50" practice mode missing** — The platform currently only supports "Biletlar 20" (20 questions per bilet, 25 minutes). Users need a "Biletlar 50" practice mode that groups questions into bilets of 50 (1200 total / 50 = 24 bilets) with a 60-minute timer. This mode is for practice only (mashq) and does not affect statistics or results. It replaces the "tez test" (quick test) button on the testlar.php page.

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN an admin edits a question with an image upload via `enctype="multipart/form-data"` THEN the system executes the UPDATE query with a parameter binding approach that may incorrectly bind or lose the `bilet_id` value, causing the question to disassociate from its ticket

1.2 WHEN an admin edits a question and the `bilet_id` field is not properly received in POST data (e.g., due to multipart form handling edge cases) THEN the system defaults `bilet_id` to `1` instead of preserving the existing value, silently moving the question to ticket #1

1.3 WHEN a user is taking a test and clicks on an answer for a question that has already been answered THEN the system allows the previous selection to be replaced with the new selection without any restriction

1.4 WHEN a user presses keyboard shortcuts (F1-F4, 1-4) for a question that already has a selected answer THEN the system allows the answer to be changed via keyboard input as well

1.5 WHEN a user visits the testlar.php page THEN the system only displays "Biletlar 20" (20 questions per bilet, 60 bilets) with no option for a 50-question practice mode

1.6 WHEN a user wants to practice with larger question sets THEN the system provides no "Biletlar 50" mode — only the standard 20-question bilets and a "tez test" (quick test) with 20 random questions are available

### Expected Behavior (Correct)

2.1 WHEN an admin edits a question with an image upload via `enctype="multipart/form-data"` THEN the system SHALL correctly preserve the `bilet_id` value in the UPDATE query, keeping the question associated with its original ticket

2.2 WHEN an admin edits a question and saves THEN the system SHALL ensure the `bilet_id` parameter is correctly bound in the prepared statement so that the question remains in its assigned ticket after the update

2.3 WHEN a user is taking a test and clicks on an answer for a question that has already been answered THEN the system SHALL ignore the click and keep the original answer selection unchanged

2.4 WHEN a user presses keyboard shortcuts (F1-F4, 1-4) for a question that already has a selected answer THEN the system SHALL ignore the keyboard input and keep the original answer unchanged

2.5 WHEN a user visits the testlar.php page THEN the system SHALL display a "Biletlar 50" section/tab alongside the existing "Biletlar 20" section, showing 24 bilets (1200 questions / 50 questions per bilet)

2.6 WHEN a user starts a "Biletlar 50" bilet THEN the system SHALL load 50 questions for that bilet and set the timer to 60 minutes (1 hour)

2.7 WHEN a user completes a "Biletlar 50" test THEN the system SHALL NOT save the result to natijalar (statistics/results) — this mode is practice-only (mashq)

2.8 WHEN the "Biletlar 50" section is displayed on testlar.php THEN the system SHALL replace the existing "tez test" (quick test) button with the "Biletlar 50" functionality

### Unchanged Behavior (Regression Prevention)

3.1 WHEN an admin edits a question without uploading an image (no file in the form) THEN the system SHALL CONTINUE TO update the question fields correctly and preserve the `bilet_id` association

3.2 WHEN an admin creates a new question (INSERT path) with or without an image upload THEN the system SHALL CONTINUE TO insert the question with the correct `bilet_id` and all fields properly saved

3.3 WHEN a user is taking a test and selects an answer for a question that has NOT been answered yet THEN the system SHALL CONTINUE TO accept the selection, highlight it, show correct/wrong feedback, and record the answer

3.4 WHEN a user navigates between questions using Next/Previous buttons or the question grid THEN the system SHALL CONTINUE TO display previously selected answers as highlighted and allow free navigation

3.5 WHEN a user finishes the test and submits THEN the system SHALL CONTINUE TO correctly calculate the score based on all recorded answers and save the result

3.6 WHEN a user views the testlar.php page with "Biletlar 20" THEN the system SHALL CONTINUE TO display the existing 60 bilets of 20 questions each with 25-minute timer functionality unchanged

3.7 WHEN the admin panel is accessed THEN the system SHALL CONTINUE TO show no changes — "Biletlar 50" is only visible in the user panel and does not affect admin functionality

3.8 WHEN a user completes a standard "Biletlar 20" test THEN the system SHALL CONTINUE TO save the result to natijalar and update user statistics (tests_taken, best_score) as before

3.9 WHEN questions are managed in the admin panel THEN the system SHALL CONTINUE TO use the existing bilet_id assignment (1-60 range for "Biletlar 20") without interference from the "Biletlar 50" grouping logic

---

## Bug Condition Derivation

### Bug 1: Question disappears on edit with image upload

```pascal
FUNCTION isBugCondition_EditQuestion(X)
  INPUT: X of type QuestionEditRequest
  OUTPUT: boolean
  
  // Bug triggers when editing (UPDATE path) with multipart form data
  RETURN X.is_edit = true AND X.has_file_upload = true
END FUNCTION
```

```pascal
// Property: Fix Checking - Question preserves bilet_id on edit with image
FOR ALL X WHERE isBugCondition_EditQuestion(X) DO
  result <- updateQuestion(X)
  ASSERT result.bilet_id = X.submitted_bilet_id AND result.success = true
END FOR
```

```pascal
// Property: Preservation Checking - Edits without file upload still work
FOR ALL X WHERE NOT isBugCondition_EditQuestion(X) DO
  ASSERT F(X) = F'(X)
END FOR
```

### Bug 2: User can re-select answers

```pascal
FUNCTION isBugCondition_AnswerReselect(X)
  INPUT: X of type AnswerClickEvent
  OUTPUT: boolean
  
  // Bug triggers when user clicks on an answer for an already-answered question
  RETURN X.question_already_answered = true
END FUNCTION
```

```pascal
// Property: Fix Checking - Re-selection is blocked
FOR ALL X WHERE isBugCondition_AnswerReselect(X) DO
  result <- handleAnswerClick(X)
  ASSERT result.answer_changed = false AND result.stored_answer = X.original_answer
END FOR
```

```pascal
// Property: Preservation Checking - First selection still works
FOR ALL X WHERE NOT isBugCondition_AnswerReselect(X) DO
  ASSERT F(X) = F'(X)
END FOR
```

### Feature 3: "Biletlar 50" practice mode missing

```pascal
FUNCTION isBugCondition_Biletlar50(X)
  INPUT: X of type TestSessionRequest
  OUTPUT: boolean
  
  // "Bug" triggers when user requests a Biletlar 50 practice session
  RETURN X.mode = "biletlar_50"
END FUNCTION
```

```pascal
// Property: Fix Checking - Biletlar 50 mode loads 50 questions with 60-min timer, no stats
FOR ALL X WHERE isBugCondition_Biletlar50(X) DO
  result <- startTestSession(X)
  ASSERT result.question_count = 50
    AND result.timer_seconds = 3600
    AND result.saves_to_natijalar = false
    AND result.bilet_number >= 1 AND result.bilet_number <= 24
END FOR
```

```pascal
// Property: Preservation Checking - Biletlar 20 and admin panel unchanged
FOR ALL X WHERE NOT isBugCondition_Biletlar50(X) DO
  ASSERT F(X) = F'(X)
END FOR
```
