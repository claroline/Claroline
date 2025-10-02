import React, {cloneElement, useId, useState} from 'react'
import {PropTypes as T} from 'prop-types'
import {CloseButton, Collapse} from 'react-bootstrap'

import {trans} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {constants, useSize} from '#/main/app/dom/size'

const PageAside = ({
  children,
  closable = true,
  show = true
}) => {
  const asideId = useId()
  const toggleId = useId()
  const size = useSize()

  const [opened, setOpened] = useState(show && ![constants.SIZE_XS, constants.SIZE_SM, constants.SIZE_MD].includes(size))

  return (
    <div className="app-page-aside" role="presentation">
      {(closable && !opened) &&
        <Button
          id={toggleId}
          className="position-absolute top-0 start-100 z-2 m-3 p-2 btn btn-text-body border-0 bg-body focus-ring lh-1 rounded-2 fs-sm text-uppercase"
          type={CALLBACK_BUTTON}
          icon="fa fa-list fs-base"
          label={trans('summary')}
          callback={() => {
            setOpened(!opened)
            setTimeout(() => {
              document.getElementById(toggleId).focus()
            }, 0)
          }}
          aria-expanded={opened}
          aria-controls={asideId}
        />
      }

      <Collapse in={opened} dimension="width">
        <div id={asideId} className="app-page-aside-content position-relative h-100 bg-body-tertiary p-4 scroller-y scroller-thin" role="presentation">
          {(closable && opened) &&
            <CloseButton
              id={toggleId}
              className="position-absolute top-0 end-0 z-2 p-2 m-3"
              aria-label={trans('hide-menu', {}, 'actions')}
              onClick={() => {
                setOpened(!opened)
                setTimeout(() => {
                  document.getElementById(toggleId).focus()
                }, 0)
              }}
              aria-expanded={opened}
              aria-controls={asideId}
            />
          }

          {cloneElement(children, {
            autoClose: () => {
              if ([constants.SIZE_XS, constants.SIZE_SM, constants.SIZE_MD].includes(size)) {
                setOpened(!opened)
              }
            }
          })}
        </div>
      </Collapse>
    </div>
  )
}

PageAside.propTypes = {
  children: T.any,
  closable: T.bool,
  show: T.bool
}

const PageBody = ({
  children,
  embedded = false
}) =>
  <div className="app-page-body position-relative" role={!embedded ? 'main' : 'article'}>
    {children}
  </div>

PageBody.propTypes = {
  children: T.any,
  embedded: T.bool
}

export {
  PageAside,
  PageBody
}
