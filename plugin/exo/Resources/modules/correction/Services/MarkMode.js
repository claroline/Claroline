/**
 * Defines when the marks of an Exercise become available.
 */
export default class MarkMode {
  constructor(Translator) {
    this.Translator = Translator

    /**
     * The marks are available at the same time than the Correction.
     *
     * @see \UJM\ExoBundle\Entity\Mode\CorrectionMode
     *
     * @var string
     */
    this.WITH_CORRECTION = '1'

    /**
     * The marks are available once the User has validated his Paper.
     *
     * @var string
     */
    this.AFTER_END = '2'

    /**
     * The marks are never available.
     *
     * @var string
     */
    this.NEVER = '3'
  }

  /**
   * Returns the list of all MarkMode available (the value is the corresponding translation key).
   *
   * @return object
   */
  getList() {
    const list = {}

    list[this.WITH_CORRECTION] = this.Translator.trans('at_the_same_time_that_the_correction', {}, 'ujm_exo')
    list[this.AFTER_END] = this.Translator.trans('at_the_end_of_assessment', {}, 'ujm_exo')
    list[this.NEVER] = this.Translator.trans('never', {}, 'ujm_exo')

    return list
  }
}
