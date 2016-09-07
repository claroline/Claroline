/**
 * Utilities method for arrays
 */
export default class ArrayService {
  /**
   * Shuffle an array.
   *
   * @param {Array} array
   *
   * @returns {Array}
   */
  shuffle(array) {
    let currentIndex = array.length, temporaryValue, randomIndex

    // While there remain elements to shuffle...
    while (0 !== currentIndex) {
      // Pick a remaining element...
      randomIndex = Math.floor(Math.random() * currentIndex)
      currentIndex -= 1

      // And swap it with the current element.
      temporaryValue = array[currentIndex]
      array[currentIndex] = array[randomIndex]
      array[randomIndex] = temporaryValue
    }

    return array
  }

  /**
   * Return an array containing `numberItems` from `array`.
   *
   * @param {Array} array
   * @param {Number} numberItems
   *
   * @returns {Array}
   */
  pick(array, numberItems) {
    const picked = []
    for (let i = 0; i < array.length; i++) {
      if (i <= numberItems) {
        picked.push(array[i])
      }
    }

    return picked
  }
}
