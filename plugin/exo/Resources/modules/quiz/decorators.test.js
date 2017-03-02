import freeze from 'deep-freeze'
import {assertEqual} from './../utils/test'
import {decorate} from './decorators'
import {
  TYPE_QUIZ,
  QUIZ_SUMMATIVE,
  SHUFFLE_NEVER,
  SHOW_CORRECTION_AT_VALIDATION,
  SHOW_SCORE_AT_CORRECTION,
  SCORE_SUM,
  SCORE_FIXED
} from './enums'

describe('Decorator', () => {
  it('adds editor state sections and default values to quiz state', () => {
    const state = freeze({
      quiz: {
        id: '1',
        steps: ['a', 'b'],
        parameters: {
          showMetadata: false
        }
      },
      steps: {
        a: {
          id: 'a',
          title: 'Step A',
          items: ['x', 'y']
        },
        b: {
          id: 'b',
          items: ['z'],
          parameters: {
            maxAttempts: 4
          }
        }
      },
      items: {
        x: {
          id: 'x',
          type: 'foo/bar',
          hints: [
            {value: 'Foo'},
            {value: 'Bar'}
          ]
        },
        y: {
          id: 'y',
          type: 'bar/quz',
          score: {
            type: SCORE_FIXED,
            success: 5,
            failure: 2
          }
        },
        z: {
          id: 'z',
          title: 'Item Z',
          type: 'text/html'
        }
      }
    })
    assertEqual(decorate(state), {
      quiz: {
        id: '1',
        description: '',
        steps: ['a', 'b'],
        parameters: {
          type: QUIZ_SUMMATIVE,
          showMetadata: false,
          randomOrder: SHUFFLE_NEVER,
          randomPick: SHUFFLE_NEVER,
          pick: 0,
          duration: 0,
          maxAttempts: 0,
          interruptible: false,
          showCorrectionAt: SHOW_CORRECTION_AT_VALIDATION,
          correctionDate: '',
          anonymizeAttempts: false,
          showScoreAt: SHOW_SCORE_AT_CORRECTION,
          showStatistics: false,
          showFullCorrection: true
        }
      },
      steps: {
        a: {
          id: 'a',
          items: ['x', 'y'],
          title: 'Step A',
          description: '',
          parameters: {
            maxAttempts: 0
          }
        },
        b: {
          id: 'b',
          items: ['z'],
          title: '',
          description: '',
          parameters: {
            maxAttempts: 4
          }
        }
      },
      items: {
        x: {
          id: 'x',
          type: 'foo/bar',
          title: '',
          description: '',
          hints: [
            {
              value: 'Foo',
              penalty: 0
            },
            {
              value: 'Bar',
              penalty: 0
            }
          ],
          feedback: '',
          score: {
            type: SCORE_SUM,
            success: 1,
            failure: 0
          }
        },
        y: {
          id: 'y',
          title: '',
          description: '',
          hints: [],
          feedback: '',
          type: 'bar/quz',
          score: {
            type: SCORE_FIXED,
            success: 5,
            failure: 2
          }
        },
        z: {
          id: 'z',
          type: 'text/html',
          title: 'Item Z',
          description: '',
          hints: [],
          feedback: '',
          score: {
            type: SCORE_SUM,
            success: 1,
            failure: 0
          }
        }
      },
      editor: {
        currentObject: {
          id: state.quiz.id,
          type: TYPE_QUIZ
        }
      }
    })
  })

  it('calls available decorator for each item type', () => {
    const state = freeze({
      quiz: {
        id: '1',
        steps: ['a']
      },
      steps: {
        a: {
          id: 'a',
          items: ['x']
        }
      },
      items: {
        x: {
          id: 'x',
          type: 'application/foo.bar+json'
        }
      }
    })
    const itemDecorators = {
      'application/foo.bar+json': item => {
        return Object.assign({}, item, {
          _foo: `${item.id}-bar`
        })
      }
    }
    assertEqual(decorate(state, itemDecorators), {
      quiz: {
        id: '1',
        steps: ['a'],
        description: '',
        parameters: {
          type: QUIZ_SUMMATIVE,
          showMetadata: true,
          randomOrder: SHUFFLE_NEVER,
          randomPick: SHUFFLE_NEVER,
          pick: 0,
          duration: 0,
          maxAttempts: 0,
          interruptible: false,
          showCorrectionAt: SHOW_CORRECTION_AT_VALIDATION,
          correctionDate: '',
          anonymizeAttempts: false,
          showScoreAt: SHOW_SCORE_AT_CORRECTION,
          showStatistics: false,
          showFullCorrection: true
        }
      },
      steps: {
        a: {
          id: 'a',
          title: '',
          description: '',
          items: ['x'],
          parameters: {
            maxAttempts: 0
          }
        }
      },
      items: {
        x: {
          id: 'x',
          type: 'application/foo.bar+json',
          title: '',
          description: '',
          hints: [],
          feedback: '',
          score: {
            type: SCORE_SUM,
            success: 1,
            failure: 0
          },
          _foo: 'x-bar'
        }
      },
      editor: {
        currentObject: {
          id: state.quiz.id,
          type: TYPE_QUIZ
        }
      }
    })
  })
})
