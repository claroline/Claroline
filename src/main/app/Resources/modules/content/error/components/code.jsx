import React, {useState} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl'
import {FormGroup} from '#/main/app/content/form/components/group'
import {PasswordInput} from '#/main/app/data/types/password/components/input'
import {Button} from '#/main/app/action'
import {ASYNC_BUTTON} from '#/main/app/buttons'

import {ContentError} from '#/main/app/content/error/components/error'

/**
 * An error component to display when a content is locked by an access code.
 * It displays a form to submit the access code.
 */
const ContentErrorCode = ({contentName, backAction, submitAccessCode, contactEmail}) => {
  const [accessCode, setAccessCode] = useState('')

  return (
    <ContentError
      title={trans('error_access_code')}
      description={trans('error_access_code_desc', {contentName: `<b>${contentName}</b>`})}
      help={trans('error_access_code_contact', {contactLink: contactEmail ?
        `(<a href="mailto:${contactEmail}">${contactEmail}</a>)` : ''
      })}
    >
      <form onSubmit={(e) => e.preventDefault()}>
        <FormGroup
          id="access-code"
          label={trans('access_code')}
          className="col-6 mx-auto mt-5"
        >
          <PasswordInput
            id="access-code"
            value={accessCode}
            onChange={setAccessCode}
            disablePasswordCheck={true}
            hideStrength={true}
            autoComplete="off"
            autoFocus={true}
            size="lg"
          />
        </FormGroup>

        <div className="col-8 mt-5 mx-auto d-flex flex-wrap gap-2 justify-content-center">
          <Button
            className="btn btn-link"
            label={trans('back', {}, 'actions')}
            icon="fa fa-arrow-left"
            {...backAction}
            size="lg"
          />
          <Button
            className={classes('btn btn-primary', {'btn-wave': !isEmpty(accessCode)})}
            type={ASYNC_BUTTON}
            htmlType="submit"
            disabled={isEmpty(accessCode)}
            label={trans('unlock_resource', {}, 'actions')}
            async={() => submitAccessCode(accessCode)}
            size="lg"
          />
        </div>
      </form>
    </ContentError>
  )
}

ContentErrorCode.propTypes = {
  contentName: T.string.isRequired,
  backAction: T.object,
  submitAccessCode: T.func.isRequired,
  contactEmail: T.string
}

export {
  ContentErrorCode
}
