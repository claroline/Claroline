import sanitize from './sanitizers'
import {ensure} from '#/main/core/scaffolding/tests'

describe('sanitize quiz', () => {
  it('converts numeric fields to integers', () => {
    ensure.equal(
      sanitize.quiz('parameters.duration', '12'),
      {parameters: {duration: 12}}
    )
    ensure.equal(
      sanitize.quiz('parameters.maxAttempts', '56'),
      {parameters: {maxAttempts: 56}}
    )
  })
})

describe('sanitize step', () => {
  it('converts numeric fields to integers', () => {
    const step = {
      title: 'foo',
      parameters: {
        maxAttempts: '123'
      }
    }
    ensure.equal(sanitize.step(step), {
      title: 'foo',
      parameters: {
        maxAttempts: 123
      }
    })
  })
})
