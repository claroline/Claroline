import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {makeId} from '#/main/core/scaffolding/id'
import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {HtmlText} from '#/main/core/layout/components/html-text'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors} from '#/main/core/administration/parameters/main/store/selectors'
import {
  ConnectionMessage as ConnectionMessageType,
  Slide as SlideType
} from '#/main/core/administration/parameters/main/prop-types'
import {constants} from '#/main/core/administration/parameters/main/constants'
import {MODAL_SLIDE_FORM} from '#/main/core/administration/parameters/main/modals/slide'

const restrictedByDates = (message) => get(message, 'restrictions.enableDates') || !isEmpty(get(message, 'restrictions.dates'))
const restrictedByRoles = (message) => get(message, 'restrictions.enableRoles') || !isEmpty(get(message, 'restrictions.roles'))

const SlidesForm = (props) =>
  <div className="slides-form">
    {0 === props.slides.length &&
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_content')}
      />
    }

    {0 < props.slides.length &&
      <ul className="slides">
        {props.slides.map((slide, slideIndex) =>
          <li key={slide.id} className="slide-preview">
            {slide.poster && slide.poster.url ?
              <img
                className="image-thumbnail"
                src={asset(slide.poster.url)}
                alt={slide.title}
              /> :
              <HtmlText className="text-thumbnail">
                {slide.content || slide.title}
              </HtmlText>
            }

            {!props.disabled &&
              <Toolbar
                id={`${slide.id}-btn`}
                className="slide-actions"
                buttonName="btn"
                tooltip="bottom"
                size="sm"
                toolbar="more"
                actions={[
                  {
                    name: 'edit',
                    type: MODAL_BUTTON,
                    icon: 'fa fa-fw fa-pencil',
                    label: trans('edit', {}, 'actions'),
                    modal: [MODAL_SLIDE_FORM, {
                      formName: selectors.STORE_NAME+'.messages.current',
                      dataPart: `slides.${slideIndex}`,
                      title: trans('content_edition')
                    }]
                  }, {
                    name: 'delete',
                    type: CALLBACK_BUTTON,
                    icon: 'fa fa-fw fa-trash-o',
                    label: trans('delete', {}, 'actions'),
                    callback: () => {
                      const newSlides = cloneDeep(props.slides)
                      newSlides.splice(slideIndex, 1)
                      props.updateProp('slides', newSlides)
                    },
                    dangerous: true
                  }
                ]}
              />
            }
          </li>
        )}
      </ul>
    }

    <CallbackButton
      className="btn btn-block btn-emphasis component-container"
      primary={true}
      disabled={props.disabled}
      callback={() => {
        const length = props.slides.length
        const newSlides = cloneDeep(props.slides)
        newSlides.push({
          id: makeId()
        })
        props.updateProp('slides', newSlides)

        props.createSlide(length)
      }}
    >
      {trans('add_content')}
    </CallbackButton>
  </div>

SlidesForm.propTypes = {
  slides: T.arrayOf(T.shape(SlideType.propTypes)).isRequired,
  disabled: T.bool.isRequired,
  createSlide: T.func.isRequired,
  updateProp: T.func.isRequired
}

SlidesForm.defaultProps = {
  slides: [],
  disabled: true
}

const MessageComponent = (props) => {
  return (
    <FormData
      level={2}
      title={props.new ? trans('connection_message_creation') : trans('connection_message_edition')}
      name={selectors.STORE_NAME+'.messages.current'}
      target={(message, isNew) => isNew ?
        ['apiv2_connectionmessage_create'] :
        ['apiv2_connectionmessage_update', {id: message.id}]
      }
      buttons={true}
      disabled={props.message.locked}
      cancel={{
        type: LINK_BUTTON,
        target: props.path+'/messages',
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true
            }, {
              name: 'type',
              type: 'choice',
              label: trans('type'),
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: constants.MESSAGE_TYPES
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            {
              name: 'restrictions.enableDates',
              label: trans('restrict_by_dates'),
              type: 'boolean',
              calculated: restrictedByDates,
              onChange: activated => {
                if (!activated) {
                  props.updateProp('restrictions.dates', [])
                }
              },
              linked: [
                {
                  name: 'restrictions.dates',
                  type: 'date-range',
                  label: trans('access_dates'),
                  displayed: restrictedByDates,
                  required: true,
                  options: {
                    time: true
                  }
                }
              ]
            }, {
              name: 'restrictions.enableRoles',
              label: trans('restrict_by_roles'),
              type: 'boolean',
              calculated: restrictedByRoles,
              onChange: activated => {
                if (!activated) {
                  props.updateProp('restrictions.roles', [])
                }
              },
              linked: [
                {
                  name: 'restrictions.roles',
                  label: trans('roles'),
                  type: 'roles',
                  displayed: restrictedByRoles,
                  required: true
                }
              ]
            }
          ]
        }
      ]}
    >
      <SlidesForm
        slides={props.message.slides || []}
        disabled={props.message.locked}
        createSlide={props.createSlide}
        updateProp={props.updateProp}
      />
    </FormData>
  )
}

MessageComponent.propTypes = {
  path: T.string,
  new: T.bool,
  message: T.shape(
    ConnectionMessageType.propTypes
  ),
  createSlide: T.func.isRequired,
  updateProp: T.func.isRequired
}

const Message = connect(
  (state) => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME+'.messages.current')),
    message: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.messages.current'))
  }),
  (dispatch) => ({
    createSlide(slideIndex) {
      dispatch(
        modalActions.showModal(MODAL_SLIDE_FORM, {
          formName: selectors.STORE_NAME+'.messages.current',
          dataPart: `slides.${slideIndex}`,
          title: trans('content_creation')
        })
      )
    },
    updateProp(prop, value) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.messages.current', prop, value))
    }
  })
)(MessageComponent)

export {
  Message
}
