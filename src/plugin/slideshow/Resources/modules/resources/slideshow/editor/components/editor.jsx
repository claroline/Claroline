import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {actions as formActions} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {Button} from '#/main/app/action/components/button'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {selectors} from '#/plugin/slideshow/resources/slideshow/editor/store'
import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'
import {MODAL_SLIDE} from '#/plugin/slideshow/resources/slideshow/editor/modals/slide'
import {Slides} from '#/plugin/slideshow/resources/slideshow/components/slides'

const EditorComponent = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.FORM_NAME}
    buttons={true}
    target={(slideshow) => ['apiv2_slideshow_update', {id: slideshow.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        icon: 'fa fa-fw fa-home',
        title: trans('overview'),
        fields: [
          {
            name: 'display.showOverview',
            type: 'boolean',
            label: trans('enable_overview'),
            linked: [
              {
                name: 'display.description',
                type: 'html',
                label: trans('overview_message'),
                displayed: (slideshow) => get(slideshow, 'display.showOverview')
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'display.showControls',
            type: 'boolean',
            label: trans('show_controls', {}, 'slideshow')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-play',
        title: trans('playback'),
        fields: [
          {
            name: 'interval',
            type: 'number',
            label: trans('slide_duration', {}, 'slideshow'),
            options: {
              unit: 'ms'
            }
          }, {
            name: 'autoPlay',
            type: 'boolean',
            label: trans('auto_play', {}, 'slideshow')
          }
        ]
      }
    ]}
  >
    {0 === props.slides.length &&
      <ContentPlaceholder
        size="lg"
        icon="fa fa-image"
        title={trans('no_slide', {}, 'slideshow')}
      />
    }

    {0 !== props.slides.length &&
      <Slides
        slides={props.slides}
        actions={(slide) => [
          {
            name: 'configure',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-cog',
            label: trans('configure', {}, 'actions'),
            modal: [MODAL_SLIDE, {
              slide: slide,
              save: (updated) => {
                const updatedPos = props.slides.findIndex(current => current.id === updated.id)

                const newSlides = props.slides.slice(0)
                newSlides[updatedPos] = updated

                props.update('slides', newSlides)
              }
            }]
          }, {
            name: 'delete',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-trash',
            label: trans('delete', {}, 'actions'),
            callback: () => {
              const deletedPos = props.slides.findIndex(current => current.id === slide.id)
              if (-1 !== deletedPos) {
                const newSlides = props.slides.slice(0)
                newSlides.splice(deletedPos, 1)

                props.update('slides', newSlides)
              }
            },
            dangerous: true,
            confirm: {
              title: trans('slide_delete_confirm', {}, 'slideshow'),
              message: trans('slide_delete_message', {}, 'slideshow'),
              button: trans('delete', {}, 'actions')
            }
          }
        ]}
      />
    }

    <Button
      className="btn btn-block btn-emphasis"
      type={MODAL_BUTTON}
      primary={true}
      label={trans('add_slide', {}, 'slideshow')}
      modal={[MODAL_SLIDE, {
        save: (slide) => props.update('slides', [].concat(props.slides, [slide]))
      }]}
    />

  </FormData>

EditorComponent.propTypes = {
  path: T.string.isRequired,
  slides: T.arrayOf(T.shape(
    SlideTypes.propTypes
  )),
  update: T.func.isRequired
}

EditorComponent.defaultProps = {
  slides: []
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    slides: selectors.slides(state)
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(selectors.FORM_NAME, prop, value))
    }
  })
)(EditorComponent)

export {
  Editor
}
