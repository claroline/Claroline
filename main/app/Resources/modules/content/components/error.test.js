import {
  ensure,
  describeComponent,
  shallowComponent
} from '#/main/core/scaffolding/tests'

import {ContentError} from '#/main/app/content/components/error'

describeComponent('ContentError', ContentError,
  // required props
  [
    'text'
  ],
  // invalid props
  {
    text: {},
    inGroup: '123',
    warnOnly: '123'
  },
  // valid props
  {
    text: 'ERROR'
  },
  // custom tests
  () => {
    it('renders an error text by default', () => {
      const error = shallowComponent(ContentError, 'ContentError', {
        text: 'ERROR'
      })

      // check propTypes
      ensure.propTypesOk()

      ensure.equal(error.hasClass('error-block'), true)
      ensure.equal(error.hasClass('error-block-danger'), true)
      ensure.equal(error.text(), 'ERROR')
    })

    it('renders a simple warning if needed', () => {
      const error = shallowComponent(ContentError, 'ContentError', {
        text: 'ERROR',
        warnOnly: true
      })

      // check propTypes
      ensure.propTypesOk()

      ensure.equal(error.hasClass('error-block'), true)
      ensure.equal(error.hasClass('error-block-warning'), true)
    })
  }
)
