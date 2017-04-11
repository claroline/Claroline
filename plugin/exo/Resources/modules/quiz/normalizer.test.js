import freeze from 'deep-freeze'
import {ensure} from '#/main/core/tests'
import {normalize} from './normalizer'

describe('Normalizer', () => {
  it('produces a new, flattened data structure from raw quiz data', () => {
    const quiz = freeze({
      id: '1',
      title: 'Quiz title',
      description: 'Quiz desc',
      parameters: {},
      meta: {},
      steps: [
        {
          id: 'a',
          parameters: {},
          items: [
            {
              id: 'x',
              type: 'application/x.choice+json'
            },
            {
              id: 'y',
              type: 'application/x.open+json'
            }
          ]
        },
        {
          id: 'b',
          parameters: {},
          items: [
            {
              id: 'z',
              type: 'text/html'
            }
          ]
        }
      ]
    })
    ensure.equal(normalize(quiz), {
      quiz: {
        id: '1',
        title: 'Quiz title',
        description: 'Quiz desc',
        parameters: {},
        meta: {},
        steps: ['a', 'b']
      },
      steps: {
        a: {
          id: 'a',
          parameters: {},
          items: ['x', 'y']
        },
        b: {
          id: 'b',
          parameters: {},
          items: ['z']
        }
      },
      items: {
        x: {
          id: 'x',
          type: 'application/x.choice+json'
        },
        y: {
          id: 'y',
          type: 'application/x.open+json'
        },
        z: {
          id: 'z',
          type: 'text/html'
        }
      }
    })
  })
})
