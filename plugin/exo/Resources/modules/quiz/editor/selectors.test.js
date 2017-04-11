import freeze from 'deep-freeze'
import {ensure} from '#/main/core/tests'
import {TYPE_QUIZ, TYPE_STEP} from './../enums'
import select from './selectors'
import {tex, t} from '#/main/core/translation'

describe('Thumbnails selector', () => {
  it('returns the quiz and step thumbs with active and errors set', () => {
    ensure.equal(select.thumbnails(fixtureState1()), [
      {
        id: '1',
        title: t('parameters'),
        type: TYPE_QUIZ,
        active: false,
        hasErrors: false
      },
      {
        id: 'a',
        title: `${tex('step')} 1`,
        type: TYPE_STEP,
        active: false,
        hasErrors: true
      },
      {
        id: 'b',
        title: `${tex('step')} 2`,
        type: TYPE_STEP,
        active: true,
        hasErrors: false
      }
    ])
  })
})

describe('Current object deep selector', () => {
  it('returns quiz properties if quiz is selected', () => {
    ensure.equal(select.currentObjectDeep(fixtureState2()), {
      type: TYPE_QUIZ,
      id: '1'
    })
  })

  it('returns step details if step is selected', () => {
    ensure.equal(select.currentObjectDeep(fixtureState3()), {
      type: TYPE_STEP,
      id: 'b',
      title: 'B',
      description: 'B desc',
      parameters: {
        maxAttempts: 5
      },
      items: [
        {
          id: 'x',
          type: 'text/html'
        }
      ]
    })
  })
})

describe('Step open panel selector', () => {
  it('returns false if no step is selected', () => {
    ensure.equal(select.stepOpenPanel(fixtureState4()), false)
  })

  it('returns open panel key of current step', () => {
    ensure.equal(select.stepOpenPanel(fixtureState5()), 'bar')
  })
})

describe('Next object selector', () => {
  it('returns the quiz if quiz is already current', () => {
    ensure.equal(select.nextObject(fixtureState2()), {
      id: '1',
      type: TYPE_QUIZ
    })
  })

  it('returns the quiz if there is only one the step', () => {
    ensure.equal(select.nextObject(fixtureState6()), {
      id: '1',
      type: TYPE_QUIZ
    })
  })

  it('returns the next step if there is one', () => {
    ensure.equal(select.nextObject(fixtureState7()), {
      id: 'b',
      type: TYPE_STEP
    })
  })

  it('returns the previous step if current is the second and last step', () => {
    ensure.equal(select.nextObject(fixtureState8()), {
      id: 'a',
      type: TYPE_STEP
    })
  })
})

describe('Valid selector', () => {
  it('returns false in case of item errors', () => {
    ensure.equal(select.valid(fixtureState1()), false)
  })

  it('returns false in case of quiz errors', () => {
    ensure.equal(select.valid(fixtureState2()), false)
  })

  it('returns true if no errors', () => {
    ensure.equal(select.valid(fixtureState3()), true)
  })
})

function fixtureState1() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a', 'b']
    },
    steps: {
      'a': {
        id: 'a',
        items: ['x']
      },
      'b': {
        id: 'b',
        items: []
      }
    },
    items: {
      x: {
        _errors: {foo: 'bar'}
      }
    },
    editor: {
      currentObject: {
        id: 'b',
        type: TYPE_STEP
      }
    }
  })
}

function fixtureState2() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a', 'b'],
      _errors: {bar: 'baz'}
    },
    steps: {
      'a': {
        id: 'a',
        items: []
      },
      'b': {
        id: 'b',
        items: []
      }
    },
    items: {},
    editor: {
      currentObject: {
        id: '1',
        type: TYPE_QUIZ
      }
    }
  })
}

function fixtureState3() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a', 'b']
    },
    steps: {
      'a': {
        id: 'a',
        items: []
      },
      'b': {
        id: 'b',
        title: 'B',
        description: 'B desc',
        parameters: {maxAttempts: 5},
        items: ['x']
      }
    },
    items: {
      'x': {
        id: 'x',
        type: 'text/html'
      }
    },
    editor: {
      currentObject: {
        id: 'b',
        type: TYPE_STEP
      }
    }
  })
}

function fixtureState4() {
  return freeze({
    editor: {
      currentObject: {
        id: 'a',
        type: TYPE_QUIZ
      },
      openPanels: {
        [TYPE_QUIZ]: 'foo',
        [TYPE_STEP]: {}
      }
    }
  })
}

function fixtureState5() {
  return freeze({
    editor: {
      currentObject: {
        id: 'b',
        type: TYPE_STEP
      },
      openPanels: {
        [TYPE_QUIZ]: false,
        [TYPE_STEP]: {
          'a': 'foo',
          'b': 'bar'
        }
      }
    }
  })
}

function fixtureState6() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a']
    },
    steps: {
      'a': {
        id: 'a',
        items: []
      }
    },
    editor: {
      currentObject: {
        id: 'a',
        type: TYPE_STEP
      }
    }
  })
}

function fixtureState7() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a', 'b']
    },
    steps: {
      'a': {
        id: 'a',
        items: []
      },
      'b': {
        id: 'b',
        items: []
      }
    },
    items: {},
    editor: {
      currentObject: {
        id: 'a',
        type: TYPE_STEP
      }
    }
  })
}

function fixtureState8() {
  return freeze({
    quiz: {
      id: '1',
      steps: ['a', 'b']
    },
    steps: {
      'a': {
        id: 'a',
        items: []
      },
      'b': {
        id: 'b',
        items: []
      }
    },
    items: {},
    editor: {
      currentObject: {
        id: 'b',
        type: TYPE_STEP
      }
    }
  })
}
