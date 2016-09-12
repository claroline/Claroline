/**
 * Defines when the corrections of an Exercise become available.
 */
export default class CorrectionMode {
  constructor(Translator) {
    this.Translator = Translator

    /**
     * The solutions are available once the User has validated his Paper.
     *
     * @var string
     */
    this.AFTER_END = '1'

    /**
     * The solutions are available once the User has validated his Paper for his last attempt
     * (Exercise must define `maxAttempts`).
     *
     * @var string
     */
    this.AFTER_LAST_ATTEMPT = '2'

    /**
     * The solutions are available after a fixed date
     * (Exercise must define `dateCorrection`).
     *
     * @var string
     */
    this.AFTER_DATE = '3'

    /**
     * The solutions will never be available to Users.
     *
     * @var string
     */
    this.NEVER = '4'
  }

  /**
   * Returns the list of all CorrectionMode available (the value is the corresponding translation key).
   *
   * @return object
   */
  getList() {
    const list = {}

    list[this.AFTER_END] = this.Translator.trans('at_the_end_of_assessment', {}, 'ujm_exo')
    list[this.AFTER_LAST_ATTEMPT] = this.Translator.trans('after_the_last_attempt', {}, 'ujm_exo')
    list[this.AFTER_DATE] = this.Translator.trans('from', {}, 'ujm_exo')
    list[this.NEVER] = this.Translator.trans('never', {}, 'ujm_exo')

    return list
  }
}
