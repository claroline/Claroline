import React from 'react'
import freeze from 'deep-freeze'
import merge from 'lodash/merge'
import {shallow, mount} from 'enzyme'
import {spyConsole, renew, ensure, mockTranslator} from '#/main/core/tests'
import {actions} from './../../quiz/editor/actions'
import {SCORE_SUM, SCORE_FIXED} from './../../quiz/enums'
import {lastId, lastIds} from './../../utils/utils'
import {actions as subActions} from './editor'
import definition from './index'

describe('Choice reducer', () => {
  const reduce = definition.editor.reduce

  it('augments and decorates base question on creation', () => {
    const item = {
      id: '1',
      type: 'application/x.choice+json',
      content: 'Question?'
    }
    const reduced = reduce(item, actions.createItem('1', 'application/x.choice+json'))
    const ids = lastIds(2)
    ensure.equal(reduced, {
      id: '1',
      type: 'application/x.choice+json',
      content: 'Question?',
      multiple: false,
      random: false,
      choices: [
        {
          id: ids[0],
          type: 'text/html',
          data: '',
          _score: 1,
          _feedback: '',
          _checked: true,
          _deletable: false
        },
        {
          id: ids[1],
          type: 'text/html',
          data: '',
          _score: 0,
          _feedback: '',
          _checked: false,
          _deletable: false
        }
      ],
      solutions: [
        {
          id: ids[0],
          score: 1,
          feedback: ''
        },
        {
          id: ids[1],
          score: 0,
          feedback: ''
        }
      ]
    })
  })

  it('updates base properties', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('random', true))
    const expected = makeFixture({random: true})
    ensure.equal(reduced, expected)
  })

  it('sanitizes incoming data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('score.success', '123'))
    const expected = makeFixture({score: {success: 123}})
    ensure.equal(reduced, expected)
  })

  it('updates choice data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateChoice('2', 'data', 'Bar updated'))
    const expected = makeFixture({choices: [{}, {data: 'Bar updated'}, {}]})
    ensure.equal(reduced, expected)
  })

  it('updates score data', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateChoice('1', 'score', 123))
    const expected = makeFixture({
      choices: [{_score: 123}, {}, {}],
      solutions: [{score: 123}, {}, {}]
    })
    ensure.equal(reduced, expected)
  })

  it('sets choice ticks on multiple prop update', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('multiple', true))
    const expected = makeFixture({
      multiple: true,
      choices: [{}, {}, {_checked: true}]
    })
    ensure.equal(reduced, expected)
  })

  it('sets choice ticks on score update', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateChoice('1', 'score', 0))
    const expected = makeFixture({
      choices: [{_checked: false, _score: 0}, {}, {_checked: true}],
      solutions: [{score: 0}, {}, {}]
    })
    ensure.equal(reduced, expected)
  })

  it('sets choice scores on score type update', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.updateProperty('score.type', SCORE_FIXED))
    const expected = makeFixture({
      choices: [{_score: 1}, {}, {_score: 0}],
      solutions: [{score: 1}, {}, {score: 0}],
      score: {type: SCORE_FIXED}
    })
    ensure.equal(reduced, expected)
  })

  it('sets choice scores and ticks on check in fixed mode (unique)', () => {
    const item = makeFixture({score: {type: SCORE_FIXED}})
    const reduced = reduce(item, subActions.updateChoice('2', 'checked', true))
    const expected = makeFixture({
      choices: [
        {_score: 0, _checked: false},
        {_score: 1, _checked: true},
        {_score: 0}
      ],
      solutions: [
        {score: 0},
        {score: 1},
        {score: 0}
      ],
      score: {type: SCORE_FIXED}
    })
    ensure.equal(reduced, expected)
  })

  it('sets choice scores and ticks on check in fixed mode (multiple)', () => {
    const item = makeFixture({
      multiple: true,
      choices: [
        {_score: 1},
        {_score: 0},
        {_score: 1, _checked: true}
      ],
      solutions: [
        {score: 1},
        {score: 0},
        {score: 1}
      ],
      score: {type: SCORE_FIXED}
    })
    const reduced = reduce(item, subActions.updateChoice('2', 'checked', true))
    const expected = makeFixture({
      multiple: true,
      choices: [
        {_score: 1},
        {_score: 1, _checked: true},
        {_score: 1, _checked: true}
      ],
      solutions: [
        {score: 1},
        {score: 1},
        {score: 1}
      ],
      score: {type: SCORE_FIXED}
    })
    ensure.equal(reduced, expected)
  })

  it('updates choices and solutions on choice add', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.addChoice())
    const expected = makeFixture({
      choices: [{}, {}, {},
        {
          id: lastId(),
          type: 'text/html',
          data: '',
          _feedback: '',
          _score: 0,
          _checked: false,
          _deletable: true
        }
      ],
      solutions: [{}, {}, {},
        {
          id: lastId(),
          feedback: '',
          score: 0
        }
      ]
    })
    ensure.equal(reduced, expected)
  })

  it('updates choices and solutions on choice remove', () => {
    const item = makeFixture()
    const reduced = reduce(item, subActions.removeChoice('2'))
    const expected = makeFixture({}, false)
    expected.choices.splice(1, 1)
    expected.solutions.splice(1, 1)
    expected.choices.forEach(choice => choice._deletable = false)
    ensure.equal(reduced, expected)
  })

  it('updates deletable flags on choice add', () => {
    let item = makeFixture({}, false)
    item.choices.splice(2, 1)
    item.solutions.splice(2, 1)
    item.choices.forEach(choice => choice._deletable = false)
    item = freeze(item)
    const reduced = reduce(item, subActions.addChoice())
    const expected = makeFixture({
      choices: [
        {},
        {},
        {
          id: lastId(),
          type: 'text/html',
          data: '',
          _feedback: '',
          _score: 0,
          _checked: false,
          _deletable: true
        }
      ],
      solutions: [
        {},
        {},
        {
          id: lastId(),
          feedback: '',
          score: 0
        }
      ]
    })
    ensure.equal(reduced, expected)
  })
})

describe('Choice decorator', () => {
  const decorate = definition.editor.decorate

  it('adds solution data and ui flags to choice items', () => {
    const item = freeze({
      id: '1',
      content: 'Question?',
      random: true,
      multiple: true,
      choices: [
        {
          id: '2',
          type: 'text/html',
          data: 'Foo'
        },
        {
          id: '3',
          type: 'text/html',
          data: 'Bar'
        }
      ],
      solutions: [
        {
          id: '2',
          score: 1,
          feedback: 'Feed foo'
        },
        {
          id: '3',
          score: 0,
          feedback: 'Feed bar'
        }
      ]
    })
    ensure.equal(decorate(item), {
      id: '1',
      content: 'Question?',
      random: true,
      multiple: true,
      choices: [
        {
          id: '2',
          type: 'text/html',
          data: 'Foo',
          _score: 1,
          _feedback: 'Feed foo',
          _checked: true,
          _deletable: false
        },
        {
          id: '3',
          type: 'text/html',
          data: 'Bar',
          _score: 0,
          _feedback: 'Feed bar',
          _checked: false,
          _deletable: false
        }
      ],
      solutions: [
        {
          id: '2',
          score: 1,
          feedback: 'Feed foo'
        },
        {
          id: '3',
          score: 0,
          feedback: 'Feed bar'
        }
      ]
    })
  })

  it('adds choice ticks for multiple responses', () => {
    const item = freeze({
      multiple: true,
      choices: [
        {
          id: '1',
          type: 'text/html',
          data: 'Foo'
        },
        {
          id: '2',
          type: 'text/html',
          data: 'Bar'
        },
        {
          id: '3',
          type: 'text/html',
          data: 'Baz'
        }
      ],
      solutions: [
        {
          id: '1',
          score: 1,
          feedback: 'Feed foo'
        },
        {
          id: '2',
          score: 0,
          feedback: 'Feed bar'
        },
        {
          id: '3',
          score: 2,
          feedback: 'Feed bar'
        }
      ]
    })
    const decorated = decorate(item)
    ensure.equal(decorated.choices[0]._checked, true)
    ensure.equal(decorated.choices[1]._checked, false)
    ensure.equal(decorated.choices[2]._checked, true)
  })

  it('adds choice ticks for unique responses', () => {
    const item = freeze({
      multiple: false,
      choices: [
        {
          id: '1',
          type: 'text/html',
          data: 'Foo'
        },
        {
          id: '2',
          type: 'text/html',
          data: 'Bar'
        },
        {
          id: '3',
          type: 'text/html',
          data: 'Baz'
        }
      ],
      solutions: [
        {
          id: '1',
          score: 1,
          feedback: 'Feed foo'
        },
        {
          id: '2',
          score: 0,
          feedback: 'Feed bar'
        },
        {
          id: '3',
          score: 2,
          feedback: 'Feed bar'
        }
      ]
    })
    const decorated = decorate(item)
    ensure.equal(decorated.choices[0]._checked, false)
    ensure.equal(decorated.choices[1]._checked, false)
    ensure.equal(decorated.choices[2]._checked, true)
  })
})

describe('Choice validator', () => {
  before(mockTranslator)

  const validate = definition.editor.validate

  it('checks answer data are not empty', () => {
    const errors = validate({
      choices: [
        {
          data: 'Foo',
          _score: 1
        },
        {
          data: '',
          _score: 0
        }
      ],
      score: {
        type: SCORE_SUM
      }
    })
    ensure.equal(errors, {
      choices: 'choice_empty_data_error'
    })
  })

  it('checks answer html is not empty', () => {
    const errors = validate({
      choices: [
        {
          data: 'Foo',
          _score: 1
        },
        {
          data: '<p></p>',
          _score: 0
        }
      ],
      score: {
        type: SCORE_SUM
      }
    })
    ensure.equal(errors, {
      choices: 'choice_empty_data_error'
    })
  })

  it('checks at least one answer has a positive score in sum/multiple mode', () => {
    const errors = validate({
      multiple: true,
      choices: [
        {
          data: 'Foo',
          _score: 0
        },
        {
          data: 'Bar',
          _score: 0
        }
      ],
      score: {
        type: SCORE_SUM
      }
    })
    ensure.equal(errors, {
      choices: 'sum_score_choice_at_least_one_correct_answer_error'
    })
  })

  it('checks at least one answer has a positive score in sum/unique mode', () => {
    const errors = validate({
      multiple: false,
      choices: [
        {
          data: 'Foo',
          _score: 0
        },
        {
          data: 'Bar',
          _score: 0
        }
      ],
      score: {
        type: SCORE_SUM
      }
    })
    ensure.equal(errors, {
      choices: 'sum_score_choice_no_correct_answer_error'
    })
  })

  it('checks success score is above failure in fixed mode', () => {
    const errors = validate({
      multiple: false,
      choices: [
        {
          data: 'Foo',
          _score: 0
        },
        {
          data: 'Bar',
          _score: 1
        }
      ],
      score: {
        type: SCORE_FIXED,
        success: 0,
        failure: 2
      }
    })
    ensure.equal(errors, {
      score: {
        failure: 'fixed_failure_above_success_error',
        success: 'fixed_success_under_failure_error'
      }
    })
  })

  it('checks at least one answer has a positive score in fixed/multiple mode', () => {
    const errors = validate({
      multiple: true,
      choices: [
        {
          data: 'Foo',
          _score: 0
        },
        {
          data: 'Bar',
          _score: 0
        }
      ],
      score: {
        type: SCORE_FIXED,
        success: 2,
        failure: 0
      }
    })
    ensure.equal(errors, {
      choices: 'fixed_score_choice_at_least_one_correct_answer_error'
    })
  })

  it('checks at least one answer has a positive score in fixed/unique mode', () => {
    const errors = validate({
      multiple: false,
      choices: [
        {
          data: 'Foo',
          _score: 0
        },
        {
          data: 'Bar',
          _score: 0
        }
      ],
      score: {
        type: SCORE_FIXED,
        success: 2,
        failure: 0
      }
    })
    ensure.equal(errors, {
      choices: 'fixed_score_choice_no_correct_answer_error'
    })
  })

  it('returns no errors if item is valid', () => {
    const errors = validate({
      multiple: false,
      choices: [
        {
          data: 'Foo',
          _score: 1
        },
        {
          data: 'Bar',
          _score: 0
        }
      ],
      score: {
        type: SCORE_FIXED,
        success: 2,
        failure: 0
      }
    })
    ensure.equal(errors, {})
  })
})

describe('<Choice/>', () => {
  const Choice = definition.editor.component

  beforeEach(() => {
    spyConsole.watch()
    renew(Choice, 'Choice')
  })
  afterEach(spyConsole.restore)

  it('has required props', () => {
    shallow(<Choice item={{score: {}}}/>)
    ensure.missingProps('Choice', ['item.id', 'validating', 'onChange'])
  })

  it('has typed props', () => {
    shallow(
      <Choice
        item={{
          id: [],
          score: {}
        }}
        validating={123}
        onChange={false}
      />
    )
    ensure.invalidProps('Choice', ['item.id', 'validating', 'onChange'])
  })

  it('renders a list of choices', () => {
    mount(
      <Choice
        item={{
          id: '1',
          content: 'Question?',
          random: true,
          multiple: true,
          choices: [
            {
              id: '2',
              type: 'text/html',
              data: 'Foo',
              _score: 1,
              _feedback: 'Feed foo',
              _checked: false,
              _deletable: false
            },
            {
              id: '3',
              type: 'text/html',
              data: 'Bar',
              _score: 0,
              _feedback: 'Feed bar',
              _checked: true,
              _deletable: false
            }
          ],
          score: {
            type: 'sum',
            success: 1,
            failure: 0
          }
        }}
        validating={false}
        onChange={() => {}}
      />
    )
    ensure.propTypesOk()
  })
})

function makeFixture(props = {}, frozen = true) {
  const fixture = merge({
    id: '1',
    type: 'application/x.choice+json',
    content: 'Question?',
    multiple: false,
    random: false,
    choices: [
      {
        id: '1',
        type: 'text/html',
        data: 'Foo',
        _score: 2,
        _feedback: 'Feedback foo',
        _checked: true,
        _deletable: true
      },
      {
        id: '2',
        type: 'text/html',
        data: 'Bar',
        _score: 0,
        _feedback: 'Feedback bar',
        _checked: false,
        _deletable: true
      },
      {
        id: '3',
        type: 'text/html',
        data: 'Baz',
        _score: 1.5,
        _feedback: 'Feedback baz',
        _checked: false,
        _deletable: true
      }
    ],
    solutions: [
      {
        id: '1',
        score: 2,
        feedback: 'Feedback foo'
      },
      {
        id: '2',
        score: 0,
        feedback: 'Feedback bar'
      },
      {
        id: '3',
        score: 1.5,
        feedback: 'Feedback baz'
      }
    ],
    score: {
      type: SCORE_SUM,
      success: 1,
      failure: 0
    }
  }, props)

  return frozen ? freeze(fixture) : fixture
}
