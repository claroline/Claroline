import React from 'react'
import {PropTypes as T} from 'prop-types'
import cloneDeep from 'lodash/cloneDeep'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {makeId} from '#/main/core/scaffolding/id'
import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/main/core/administration/parameters/store/selectors'
import {
  ConnectionMessage as ConnectionMessageTypes,
  Slide as SlideTypes
} from '#/main/core/data/types/connection-message/prop-types'

import {constants} from '#/main/core/data/types/connection-message/constants'
import {MODAL_SLIDE_FORM} from '#/main/core/administration/parameters/modals/slide'

const restrictedByDates = (message) => get(message, 'restrictions.enableDates') || (!isEmpty(get(message, 'restrictions.dates')) && (!isEmpty(get(message, 'restrictions.dates.0')) || !isEmpty(get(message, 'restrictions.dates.1'))))
const restrictedByRoles = (message) => get(message, 'restrictions.enableRoles') || !isEmpty(get(message, 'restrictions.roles'))

const SlidesForm = (props) =>
  <div className="slides-form">
    {0 === props.slides.length &&
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_content')}
      />
    }

    {0 < props.slides.length &&
      <ul className="slides">
        {props.slides.map((slide, slideIndex) =>
          <li key={slide.id} className="slide-preview">
            {slide.poster ?
              <img
                className="image-thumbnail"
                src={asset(slide.poster)}
                alt={slide.title}
              /> :
              <ContentHtml className="text-thumbnail">
                {slide.content || slide.title}
              </ContentHtml>
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
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )).isRequired,
  disabled: T.bool.isRequired,
  createSlide: T.func.isRequired,
  updateProp: T.func.isRequired
}

SlidesForm.defaultProps = {
  slides: [],
  disabled: true
}

const Message = (props) =>
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
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden')
          }, {
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

Message.propTypes = {
  path: T.string,
  new: T.bool,
  message: T.shape(
    ConnectionMessageTypes.propTypes
  ),
  createSlide: T.func.isRequired,
  updateProp: T.func.isRequired
}

export {
  Message
}
