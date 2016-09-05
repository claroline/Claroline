/**
 * Manages hints for a Question.
 */
export default class HintsCtrl {
  /**
   * Constructor.
   * 
   * @param {HintService} HintService
   */
  constructor(HintService) {
    this.HintService = HintService

    // Initialize Hints value from Paper
    for (let i = 0; i < this.hints.length; i++) {
      if (!this.hints[i].value) {
        const used = this.HintService.getHintFromPaper(this.usedHints, this.hints[i])
        if (used) {
          this.hints[i].value = used.value
        }
      }
    }
  }

  isHintUsed(hint) {
    return this.HintService.isHintUsed(this.usedHints, hint)
  }

  showHint(hint) {
    if (!this.isHintUsed(hint)) {
      // Load Hint data
      this.HintService.useHint(this.usedHints, hint)
    }
  }
}
