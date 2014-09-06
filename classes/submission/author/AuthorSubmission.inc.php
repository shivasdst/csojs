<?php

/**
 * @file classes/submission/author/AuthorSubmission.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmission
 * @ingroup submission
 * @see AuthorSubmissionDAO
 *
 * @brief AuthorSubmission class.
 */

import('classes.article.Article');

class AuthorSubmission extends Article {

	/** @var array ReviewAssignments of this article */
	var $reviewAssignments;

	/** @var array the editor decisions of this article */
	var $editorDecisions;

	/** @var array the revisions of the author file */
	var $authorFileRevisions;

	/** @var array the revisions of the editor file */
	var $editorFileRevisions;

	/** @var array the revisions of the author copyedit file */
	var $copyeditFileRevisions;

	/**
	 * Constructor.
	 */
	function AuthorSubmission() {
		parent::Article();
		$this->reviewAssignments = array();
	}

	/**
	 * Get/Set Methods.
	 */

	/**
	 * Get edit assignments for this article.
	 * @return array
	 */
	function &getEditAssignments() {
		$editAssignments =& $this->getData('editAssignments');
		return $editAssignments;
	}

	/**
	 * Set edit assignments for this article.
	 * @param $editAssignments array
	 */
	function setEditAssignments($editAssignments) {
		return $this->setData('editAssignments', $editAssignments);
	}

	/**
	 * Add a review assignment for this article.
	 * @param $reviewAssignment ReviewAssignment
	 */
	function addReviewAssignment($reviewAssignment) {
		if ($reviewAssignment->getSubmissionId() == null) {
			$reviewAssignment->setSubmissionId($this->getArticleId());
		}

		array_push($this->reviewAssignments, $reviewAssignment);
	}

	/**
	 * Remove a review assignment.
	 * @param $reviewId ID of the review assignment to remove
	 * @return boolean review assignment was removed
	 */
	function removeReviewAssignment($reviewId) {
		$reviewAssignments = array();
		$found = false;
		for ($i=0, $count=count($this->reviewAssignments); $i < $count; $i++) {
			if ($this->reviewAssignments[$i]->getReviewId() == $reviewId) {
				$found = true;
			} else {
				array_push($reviewAssignments, $this->reviewAssignments[$i]);
			}
		}
		$this->reviewAssignments = $reviewAssignments;

		return $found;
	}

	//
	// Review Assignments
	//

	/**
	 * Get review assignments for this article.
	 * @return array ReviewAssignments
	 */
	function &getReviewAssignments($round = null) {
		if ($round == null) {
			// Return an array of arrays of review assignments
			return $this->reviewAssignments;
		} else {
			// Return an array of review assignments for the specified round
			return $this->reviewAssignments[$round];
		}
	}

	/**
	 * Set review assignments for this article.
	 * @param $reviewAssignments array ReviewAssignments
	 */
	function setReviewAssignments($reviewAssignments, $round) {
		return $this->reviewAssignments[$round] = $reviewAssignments;
	}

	//
	// Editor Decisions
	//

	/**
	 * Get editor decisions.
	 * @return array
	 */
	function getDecisions($round = null) {
		if ($round == null) {
			return $this->editorDecisions;
		} else {
			return $this->editorDecisions[$round];
		}
	}

	/**
	 * Set editor decisions.
	 * @param $editorDecisions array
	 * @param $round int
	 */
	function setDecisions($editorDecisions, $round) {
		return $this->editorDecisions[$round] = $editorDecisions;
	}

	/**
	 * Get the submission status. Returns one of the defined constants
	 * (STATUS_INCOMPLETE, STATUS_ARCHIVED, STATUS_PUBLISHED,
	 * STATUS_DECLINED, STATUS_QUEUED_UNASSIGNED, STATUS_QUEUED_REVIEW,
	 * or STATUS_QUEUED_EDITING). Note that this function never returns
	 * a value of STATUS_QUEUED -- the three STATUS_QUEUED_... constants
	 * indicate a queued submission. NOTE that this code is similar to
	 * getSubmissionStatus in the SectionEditorSubmission class and
	 * changes here should be propagated.
	 */
	function getSubmissionStatus() {
		$status = $this->getStatus();
		if ($status == STATUS_ARCHIVED || $status == STATUS_PUBLISHED ||
		    $status == STATUS_DECLINED) return $status;

		// The submission is STATUS_QUEUED or the author's submission was STATUS_INCOMPLETE.
		if ($this->getSubmissionProgress()) return (STATUS_INCOMPLETE);

		// The submission is STATUS_QUEUED. Find out where it's queued.
		$editAssignments = $this->getEditAssignments();
		if (empty($editAssignments))
			return (STATUS_QUEUED_UNASSIGNED);

		$latestDecision = $this->getMostRecentDecision();
		if ($latestDecision) {
			if ($latestDecision == SUBMISSION_EDITOR_DECISION_ACCEPT || $latestDecision == SUBMISSION_EDITOR_DECISION_DECLINE) {
				return STATUS_QUEUED_EDITING;
			}
		}
		return STATUS_QUEUED_REVIEW;
	}

	/**
	 * Get the most recent decision.
	 * @return int SUBMISSION_EDITOR_DECISION_...
	 */
	function getMostRecentDecision() {
		$decisions = $this->getDecisions();
		$decision = array_pop($decisions);
		if (!empty($decision)) {
			$latestDecision = array_pop($decision);
			if (isset($latestDecision['decision'])) return $latestDecision['decision'];
		}
		return null;
	}

	//
	// Files
	//

	/**
	 * Get submission file for this article.
	 * @return ArticleFile
	 */
	function &getSubmissionFile() {
		$returner =& $this->getData('submissionFile');
		return $returner;
	}

	/**
	 * Set submission file for this article.
	 * @param $submissionFile ArticleFile
	 */
	function setSubmissionFile($submissionFile) {
		return $this->setData('submissionFile', $submissionFile);
	}

	/**
	 * Get revised file for this article.
	 * @return ArticleFile
	 */
	function &getRevisedFile() {
		$returner =& $this->getData('revisedFile');
		return $returner;
	}

	/**
	 * Set revised file for this article.
	 * @param $submissionFile ArticleFile
	 */
	function setRevisedFile($revisedFile) {
		return $this->setData('revisedFile', $revisedFile);
	}

	/**
	 * Get supplementary files for this article.
	 * @return array SuppFiles
	 */
	function &getSuppFiles() {
		$returner =& $this->getData('suppFiles');
		return $returner;
	}

	/**
	 * Set supplementary file for this article.
	 * @param $suppFiles array SuppFiles
	 */
	function setSuppFiles($suppFiles) {
		return $this->setData('suppFiles', $suppFiles);
	}

	/**
	 * Get all author file revisions.
	 * @return array ArticleFiles
	 */
	function getAuthorFileRevisions($round = null) {
		if ($round == null) {
			return $this->authorFileRevisions;
		} else {
			return $this->authorFileRevisions[$round];
		}
	}

	/**
	 * Set all author file revisions.
	 * @param $authorFileRevisions array ArticleFiles
	 */
	function setAuthorFileRevisions($authorFileRevisions, $round) {
		return $this->authorFileRevisions[$round] = $authorFileRevisions;
	}

	/**
	 * Get all editor file revisions.
	 * @return array ArticleFiles
	 */
	function getEditorFileRevisions($round = null) {
		if ($round == null) {
			return $this->editorFileRevisions;
		} else {
			return $this->editorFileRevisions[$round];
		}
	}

	/**
	 * Set all editor file revisions.
	 * @param $editorFileRevisions array ArticleFiles
	 */
	function setEditorFileRevisions($editorFileRevisions, $round) {
		return $this->editorFileRevisions[$round] = $editorFileRevisions;
	}

	/**
	 * Get the galleys for an article.
	 * @return array ArticleGalley
	 */
	function &getGalleys() {
		$galleys =& $this->getData('galleys');
		return $galleys;
	}

	/**
	 * Set the galleys for an article.
	 * @param $galleys array ArticleGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}

	//
	// Comments
	//

	/**
	 * Get most recent editor decision comment.
	 * @return ArticleComment
	 */
	function getMostRecentEditorDecisionComment() {
		return $this->getData('mostRecentEditorDecisionComment');
	}

	/**
	 * Set most recent editor decision comment.
	 * @param $mostRecentEditorDecisionComment ArticleComment
	 */
	function setMostRecentEditorDecisionComment($mostRecentEditorDecisionComment) {
		return $this->setData('mostRecentEditorDecisionComment', $mostRecentEditorDecisionComment);
	}

	/**
	 * Get most recent copyedit comment.
	 * @return ArticleComment
	 */
	function getMostRecentCopyeditComment() {
		return $this->getData('mostRecentCopyeditComment');
	}

	/**
	 * Set most recent copyedit comment.
	 * @param $mostRecentCopyeditComment ArticleComment
	 */
	function setMostRecentCopyeditComment($mostRecentCopyeditComment) {
		return $this->setData('mostRecentCopyeditComment', $mostRecentCopyeditComment);
	}

	/**
	 * Get most recent layout comment.
	 * @return ArticleComment
	 */
	function getMostRecentLayoutComment() {
		return $this->getData('mostRecentLayoutComment');
	}

	/**
	 * Set most recent layout comment.
	 * @param $mostRecentLayoutComment ArticleComment
	 */
	function setMostRecentLayoutComment($mostRecentLayoutComment) {
		return $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
	}

	/**
	 * Get most recent proofread comment.
	 * @return ArticleComment
	 */
	function getMostRecentProofreadComment() {
		return $this->getData('mostRecentProofreadComment');
	}

	/**
	 * Set most recent proofread comment.
	 * @param $mostRecentProofreadComment ArticleComment
	 */
	function setMostRecentProofreadComment($mostRecentProofreadComment) {
		return $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
	}

	/**
	 * Get the CSS class for highlighting this submission in a list, based on status.
	 * @return string
	 */
	function getHighlightClass() {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$overdueSeconds = 60 * 60 * 24 * 14; // Two weeks

		// Submissions that are not still queued (i.e. published) are not highlighted.
		if ($this->getStatus() != STATUS_QUEUED) return null;

		// Awaiting assignment.
		$editAssignments = $this->getEditAssignments();
		if (empty($editAssignments)) return 'highlight';

		$journal =& Request::getJournal();
		// Sanity check
		if (!$journal || $journal->getId() != $this->getJournalId()) return null;

		// Check whether it's in review or editing.
		$inEditing = false;
		$decisionsEmpty = true;
		$lastDecisionDate = null;
		$decisions = $this->getDecisions();
		$decision = array_pop($decisions);
		if (!empty($decision)) {
			$latestDecision = array_pop($decision);
			if (is_array($latestDecision)) {
				if ($latestDecision['decision'] == SUBMISSION_EDITOR_DECISION_ACCEPT) $inEditing = true;
				$decisionsEmpty = false;
				$lastDecisionDate = strtotime($latestDecision['dateDecided']);
			}
		}

		if ($inEditing) {
			// ---
			// --- Highlighting conditions for submissions in editing
			// ---

			// COPYEDITING

			// First round of copyediting
			$initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateCopyeditorNotified = $initialSignoff->getDateNotified() ?
				strtotime($initialSignoff->getDateNotified()) : 0;
			$dateCopyeditorUnderway = $initialSignoff->getDateUnderway() ?
				strtotime($initialSignoff->getDateUnderway()) : 0;
			$dateCopyeditorCompleted = $initialSignoff->getDateCompleted() ?
				strtotime($initialSignoff->getDateCompleted()) : 0;
			$dateCopyeditorAcknowledged = $initialSignoff->getDateAcknowledged() ?
				strtotime($initialSignoff->getDateAcknowledged()) : 0;
			$dateLastCopyeditor = max($dateCopyeditorNotified, $dateCopyeditorUnderway);

			// If the Copyeditor has not been notified, highlight.
			if (!$dateCopyeditorNotified) return 'highlightCopyediting';

			// Check if the copyeditor is overdue on round 1
			if (	$dateLastCopyeditor &&
				!$dateCopyeditorCompleted &&
				$dateLastCopyeditor + $overdueSeconds < time()
			) return 'highlightCopyediting';

			// Check if acknowledgement is overdue for CE round 1
			if ($dateCopyeditorCompleted && !$dateCopyeditorAcknowledged) return 'highlightCopyediting';

			// Second round of copyediting
			$authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateCopyeditorAuthorNotified = $authorSignoff->getDateNotified() ?
				strtotime($authorSignoff->getDateNotified()) : 0;
			$dateCopyeditorAuthorUnderway = $authorSignoff->getDateUnderway() ?
				strtotime($authorSignoff->getDateUnderway()) : 0;
			$dateCopyeditorAuthorCompleted = $authorSignoff->getDateCompleted() ?
				strtotime($authorSignoff->getDateCompleted()) : 0;
			$dateCopyeditorAuthorAcknowledged = $authorSignoff->getDateAcknowledged() ?
				strtotime($authorSignoff->getDateAcknowledged()) : 0;
			$dateLastCopyeditorAuthor = max($dateCopyeditorAuthorNotified, $dateCopyeditorAuthorUnderway);

			// Check if round 2 is awaiting notification.
			if ($dateCopyeditorAcknowledged && !$dateCopyeditorAuthorNotified) return 'highlightCopyediting';

			// Check if acknowledgement is overdue for CE round 2
			if ($dateCopyeditorAuthorCompleted && !$dateCopyeditorAuthorAcknowledged) return 'highlightCopyediting';

			// Check if author is overdue on CE round 2
			if (	$dateLastCopyeditorAuthor &&
				!$dateCopyeditorAuthorCompleted &&
				$dateLastCopyeditorAuthor + $overdueSeconds < time()
			) return 'highlightCopyediting';

			// Third round of copyediting
			$finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateCopyeditorFinalNotified = $finalSignoff->getDateNotified() ?
				strtotime($finalSignoff->getDateNotified()) : 0;
			$dateCopyeditorFinalUnderway = $finalSignoff->getDateUnderway() ?
				strtotime($finalSignoff->getDateUnderway()) : 0;
			$dateCopyeditorFinalCompleted = $finalSignoff->getDateCompleted() ?
				strtotime($finalSignoff->getDateCompleted()) : 0;
			$dateLastCopyeditorFinal = max($dateCopyeditorFinalNotified, $dateCopyeditorUnderway);

			// Check if round 3 is awaiting notification.
			if ($dateCopyeditorAuthorAcknowledged && !$dateCopyeditorFinalNotified) return 'highlightCopyediting';

			// Check if copyeditor is overdue on round 3
			if (	$dateLastCopyeditorFinal &&
				!$dateCopyeditorFinalCompleted &&
				$dateLastCopyeditorFinal + $overdueSeconds < time()
			) return 'highlightCopyediting';

			// Check if acknowledgement is overdue for CE round 3
			if ($dateCopyeditorFinalCompleted && !$dateCopyeditorFinalAcknowledged) return 'highlightCopyediting';

			// LAYOUT EDITING
			$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateLayoutNotified = $layoutSignoff->getDateNotified() ?
				strtotime($layoutSignoff->getDateNotified()) : 0;
			$dateLayoutUnderway = $layoutSignoff->getDateUnderway() ?
				strtotime($layoutSignoff->getDateUnderway()) : 0;
			$dateLayoutCompleted = $layoutSignoff->getDateCompleted() ?
				strtotime($layoutSignoff->getDateCompleted()) : 0;
			$dateLayoutAcknowledged = $layoutSignoff->getDateAcknowledged() ?
				strtotime($layoutSignoff->getDateAcknowledged()) : 0;
			$dateLastLayout = max($dateLayoutNotified, $dateLayoutUnderway);

			// Check if Layout Editor needs to be notified.
			if ($dateLastCopyeditorFinal && !$dateLayoutNotified) return 'highlightLayoutEditing';

			// Check if layout editor is overdue
			if (	$dateLastLayout &&
				!$dateLayoutCompleted &&
				$dateLastLayout + $overdueSeconds < time()
			) return 'highlightLayoutEditing';

			// Check if acknowledgement is overdue for layout
			if ($dateLayoutCompleted && !$dateLayoutAcknowledged) return 'highlightLayoutEditing';

			// PROOFREADING
			$signoffDao =& DAORegistry::getDAO('SignoffDAO');

			// First round of proofreading
			$authorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateAuthorNotified = $authorSignoff->getDateNotified() ?
				strtotime($authorSignoff->getDateNotified()) : 0;
			$dateAuthorUnderway = $authorSignoff->getDateUnderway() ?
				strtotime($authorSignoff->getDateUnderway()) : 0;
			$dateAuthorCompleted = $authorSignoff->getDateCompleted() ?
				strtotime($authorSignoff->getDateCompleted()) : 0;
			$dateAuthorAcknowledged = $authorSignoff->getDateAcknowledged() ?
				strtotime($authorSignoff->getDateAcknowledged()) : 0;
			$dateLastAuthor = max($dateNotified, $dateAuthorUnderway);

			// Check if the author is awaiting proofreading notification.
			if ($dateLayoutAcknowledged && !$dateAuthorNotified) return 'higlightProofreading';

			// Check if the author is overdue on round 1 of proofreading
			if (	$dateLastAuthor &&
				!$dateAuthorCompleted &&
				$dateLastAuthor + $overdueSeconds < time()
			) return 'higlightProofreading';

			// Check if acknowledgement is overdue for proofreading round 1
			if ($dateAuthorCompleted && !$dateAuthorAcknowledged) return 'higlightProofreading';

			// Second round of proofreading
			$proofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateProofreaderNotified = $proofreaderSignoff->getDateNotified() ?
				strtotime($proofreaderSignoff->getDateNotified()) : 0;
			$dateProofreaderUnderway = $proofreaderSignoff->getDateUnderway() ?
				strtotime($proofreaderSignoff->getDateUnderway()) : 0;
			$dateProofreaderCompleted = $proofreaderSignoff->getDateCompleted() ?
				strtotime($proofreaderSignoff->getDateCompleted()) : 0;
			$dateProofreaderAcknowledged = $proofreaderSignoff->getDateAcknowledged() ?
				strtotime($proofreaderSignoff->getDateAcknowledged()) : 0;
			$dateLastProofreader = max($dateProofreaderNotified, $dateProofreaderUnderway);

			// Check if the proofreader is awaiting notification.
			if ($dateAuthorAcknowledged && !$dateProofreaderNotified) return 'higlightProofreading';

			// Check if acknowledgement is overdue for proofreading round 2
			if ($dateProofreaderCompleted && !$dateProofreaderAcknowledged) return 'higlightProofreading';

			// Check if proofreader is overdue on proofreading round 2
			if (	$dateLastProofreader &&
				!$dateProofreaderCompleted &&
				$dateLastProofreader + $overdueSeconds < time()
			) return 'higlightProofreading';

			// Third round of proofreading
			$layoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $this->getId());
			$dateLayoutEditorNotified = $layoutEditorSignoff->getDateNotified() ?
				strtotime($layoutEditorSignoff->getDateNotified()) : 0;
			$dateLayoutEditorUnderway = $layoutEditorSignoff->getDateUnderway() ?
				strtotime($layoutEditorSignoff->getDateUnderway()) : 0;
			$dateLayoutEditorCompleted = $layoutEditorSignoff->getDateCompleted() ?
				strtotime($layoutEditorSignoff->getDateCompleted()) : 0;
			$dateLastLayoutEditor = max($dateLayoutEditorNotified, $dateCopyeditorUnderway);

			// Check if the layout editor is awaiting notification.
			if ($dateProofreaderAcknowledged && !$dateLayoutEditorNotified) return 'higlightProofreading';

			// Check if proofreader is overdue on round 3 of proofreading
			if (	$dateLastLayoutEditor &&
				!$dateLayoutEditorCompleted &&
				$dateLastLayoutEditor + $overdueSeconds < time()
			) return 'higlightProofreading';

			// Check if acknowledgement is overdue for proofreading round 3
			if ($dateLayoutEditorCompleted && !$dateLayoutEditorAcknowledged) return 'higlightProofreading';
		} else {
			// ---
			// --- Highlighting conditions for submissions in review
			// ---
			$reviewAssignments =& $this->getReviewAssignments($this->getCurrentRound());
			if (is_array($reviewAssignments) && !empty($reviewAssignments)) {
				$allReviewsComplete = true;
				foreach ($reviewAssignments as $i => $junk) {
					$reviewAssignment =& $reviewAssignments[$i];

					// If the reviewer has not been notified, highlight.
					if ($reviewAssignment->getDateNotified() === null) return 'highlightReviewerNotNotified';

					// Check review status.
					if (!$reviewAssignment->getCancelled() && !$reviewAssignment->getDeclined()) {
						if (!$reviewAssignment->getDateCompleted() && !$reviewAssignment->getCancelled()) $allReviewsComplete = false;

						$dateReminded = $reviewAssignment->getDateReminded() ?
							strtotime($reviewAssignment->getDateReminded()) : 0;
						$dateNotified = $reviewAssignment->getDateNotified() ?
							strtotime($reviewAssignment->getDateNotified()) : 0;
						$dateConfirmed = $reviewAssignment->getDateConfirmed() ?
							strtotime($reviewAssignment->getDateConfirmed()) : 0;

						// Check whether a reviewer is overdue to confirm invitation
						if (!$reviewAssignment->getDateCompleted() &&
							!$dateConfirmed &&
							!$journal->getSetting('remindForInvite') &&
							max($dateReminded, $dateNotified) + $overdueSeconds < time()
						) return 'highlightReviewerConfirmationOverdue';
						// Check whether a reviewer is overdue to complete review
						if (!$reviewAssignment->getDateCompleted() &&
							$dateConfirmed &&
							!$journal->getSetting('remindForSubmit') &&
							max($dateReminded, $dateConfirmed) + $overdueSeconds < time()
						) return 'highlightReviewerCompletionOverdue';
					}

					unset($reviewAssignment);
				}
				// If all reviews are complete but no decision is recorded, highlight.
				if ($allReviewsComplete && $decisionsEmpty) return 'highlightNoDecision';

				// If the author's last file upload hasn't been taken into account in
				// the most recent decision or author/editor correspondence, highlight.
				$comment = $this->getMostRecentEditorDecisionComment();
				$commentDate = $comment ? strtotime($comment->getDatePosted()) : 0;
				$authorFileRevisions = $this->getAuthorFileRevisions($this->getCurrentRound());
				$authorFileDate = null;
				if (is_array($authorFileRevisions) && !empty($authorFileRevisions)) {
					$authorFile = array_pop($authorFileRevisions);
					$authorFileDate = strtotime($authorFile->getDateUploaded());
				}
				if (	($lastDecisionDate || $commentDate) &&
					$authorFileDate &&
					$authorFileDate > max($lastDecisionDate, $commentDate)
				) return 'highlightRevisedCopyUploaded';
			}
		}
		return null;
	}
	
}

?>
