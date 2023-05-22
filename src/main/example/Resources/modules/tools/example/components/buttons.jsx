import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const ExampleButtons = (props) =>
  <Fragment>
    <div>
      {['primary', 'secondary', 'success', 'danger', 'warning', 'info'].map(type =>
        <Button
          className={classes('btn', type)}
          type={CALLBACK_BUTTON}
          label={type}
          callback={() => true}
        />
      )}
    </div>
  </Fragment>

ExampleButtons.propTypes = {

}

export {
  ExampleButtons
}
