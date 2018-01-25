export class Answerable {
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

export class CorrectedAnswer {
  constructor(expected = [], missing = [], unexpected = [], penalties = []) {
    this.expected = expected
    this.missing = missing
    this.unexpected = unexpected
    this.penalties = penalties
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

  addElement(element, property) {
    //if we didn't bother to add and id, just push the element no matter what
    if (element.getId() === null) {
      this[property].push(element)
    } else {
      //check duplicatas
      let answer = this[property].find(answer => answer.getId() === element.getId())
      if (!answer) {
        this[property].push(element)
      }
    }
  }
}
