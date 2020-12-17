import {makeId} from '#/main/core/scaffolding/id'

class Answerable {
  constructor(score = null, id = null) {
    this.score = score
    this.id = id
  }

  getScore() {
    return this.score
  }

  getId() {
    return this.id
  }
}

/**
 * A generic way to represent the correction of an item answer.
 */
class CorrectedAnswer {
  constructor(expected = [], missing = [], unexpected = [], penalties = [], expectedMissing = []) {
    this.expected = expected
    this.missing = missing
    this.unexpected = unexpected
    this.penalties = penalties
    this.expectedMissing = expectedMissing
  }

  getExpected() {
    return this.expected
  }

  addExpected(expected) {
    this.addElement(expected, 'expected')
  }

  getMissing() {
    return this.missing
  }

  addMissing(missing) {
    this.addElement(missing, 'missing')
  }

  getUnexpected() {
    return this.unexpected
  }

  addUnexpected(unexpected) {
    this.addElement(unexpected, 'unexpected')
  }

  getPenalties() {
    return this.penalties
  }

  addPenalty(penalty) {
    this.addElement(penalty, 'penalties')
  }

  getExpectedMissing() {
    return this.expectedMissing
  }

  addExpectedMissing(expectedMissing) {
    this.addElement(expectedMissing, 'expectedMissing')
  }

  addElement(element, property) {
    // if we didn't bother to add and id, just push the element no matter what
    if (element.getId() === null) {
      this[property].push(element)
    } else {
      // check duplicates
      let answer = this[property].find(answer => answer.getId() === element.getId())
      if (!answer) {
        this[property].push(element)
      }
    }
  }
}

function emptyAnswer() {
  return {
    id: makeId(),
    type: 'text/html',
    data: ''
  }
}

export {
  emptyAnswer,

  Answerable,
  CorrectedAnswer
}
