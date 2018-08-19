import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import classes from 'classnames'

import {t} from '#/main/core/translation'

import {FormGroup as FormGroupTypes} from '#/main/core/layout/form/prop-types'
import {ContentError} from '#/main/app/content/components/error'
import {ContentHelp} from '#/main/app/content/components/help'

const FormGroup = props =>
  <div className={classes('form-group', props.className, {
    'has-error': props.error && !props.warnOnly,
    'has-warning': props.error && props.warnOnly
  })}>
    {props.label &&
      <label
        className={classes('control-label', {'sr-only': props.hideLabel})}
        htmlFor={props.id}
      >
        {props.label}

        {props.optional && <small>({t('optional')})</small>}
      </label>
    }

    {props.children}

    {props.error &&
      <ContentError text={props.error} inGroup={true} warnOnly={props.warnOnly}/>
    }

    {props.help && 0 !== props.help.length &&
      <ContentHelp help={props.help} />
    }
  </div>

implementPropTypes(FormGroup, FormGroupTypes, {
  children: T.node.isRequired
})

export {
  FormGroup
}
