import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import cloneDeep from 'lodash/cloneDeep'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {makeId} from '#/main/core/scaffolding/id'
import {
  ConnectionMessage as ConnectionMessageType,
  Slide as SlideType
} from '#/main/core/administration/parameters/main/prop-types'
import {constants} from '#/main/core/administration/parameters/main/constants'
import {MODAL_SLIDE_FORM} from '#/main/core/administration/parameters/main/modals/slide'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {HtmlText} from '#/main/core/layout/components/html-text'

const SlidesForm = (props) =>
  <div className="slides-form">
    {0 === props.slides.length &&
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_content')}
      >
        {!props.disabled &&
          <CallbackButton
            className="btn"
            primary={true}
            callback={() => {
              const length = props.slides.length
              const newSlides = cloneDeep(props.slides)
              newSlides.push({
                id: makeId()
              })
              props.updateProp('slides', newSlides)

              props.createSlide(length)
            }}
            style={{marginTop: 10}}
          >
            {trans('add_content')}
          </CallbackButton>
        }
      </EmptyPlaceholder>
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
                      formName: 'messages.current',
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

    {!props.disabled && 0 < props.slides.length &&
      <CallbackButton
        className="btn"
        primary={true}
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
    }
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
  const SlidesComponent = (
    <SlidesForm
      slides={props.message.slides || []}
      disabled={props.message.locked}
      createSlide={props.createSlide}
      updateProp={props.updateProp}
    />
  )

  return (
    <FormData
      level={2}
      title={props.new ? trans('connection_message_creation') : trans('connection_message_edition')}
      name="messages.current"
      target={(message, isNew) => isNew ?
        ['apiv2_connectionmessage_create'] :
        ['apiv2_connectionmessage_update', {id: message.id}]
      }
      buttons={true}
      cancel={{
        type: LINK_BUTTON,
        target: '/messages',
        exact: true
      }}
      sections={[
        {
          title: trans('general'),
          fields: [
            {
              name: 'title',
              type: 'string',
              label: trans('title'),
              required: true,
              disabled: (message) => message.locked
            }, {
              name: 'type',
              type: 'choice',
              label: trans('type'),
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: constants.MESSAGE_TYPES
              },
              disabled: (message) => message.locked
            }, {
              name: 'restrictions.dates',
              type: 'date-range',
              label: trans('for_period'),
              required: true,
              options: {
                time: true
              },
              disabled: (message) => message.locked
            }, {
              name: 'roles',
              label: trans('roles'),
              type: 'roles',
              required: true,
              disabled: (message) => message.locked
            }, {
              name: 'content',
              label: trans('content'),
              required: true,
              component: SlidesComponent
            }
          ]
        }
      ]}
    />
  )
}

MessageComponent.propTypes = {
  new: T.bool,
  message: T.shape(ConnectionMessageType.propTypes),
  createSlide: T.func.isRequired,
  updateProp: T.func.isRequired
}

const Message = connect(
  (state) => ({
    new: formSelect.isNew(formSelect.form(state, 'messages.current')),
    message: formSelect.data(formSelect.form(state, 'messages.current'))
  }),
  (dispatch) => ({
    createSlide: (slideIndex) => dispatch(
      modalActions.showModal(MODAL_SLIDE_FORM, {
        formName: 'messages.current',
        dataPart: `slides.${slideIndex}`,
        title: trans('content_creation')
      })
    ),
    updateProp(prop, value) {
      dispatch(formActions.updateProp('messages.current', prop, value))
    }
  })
)(MessageComponent)

export {
  Message
}
