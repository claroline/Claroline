import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

import {Slide as SlideTypes} from '#/plugin/slideshow/resources/slideshow/prop-types'
import {selectors} from '#/plugin/slideshow/resources/slideshow/editor/modals/slide/store/selectors'

const SlideModal = props =>
  <Modal
    {...omit(props, 'slide', 'formData', 'isNew', 'saveEnabled', 'reset', 'save')}
    icon={classes('fa fa-fw', {
      'fa-plus': props.isNew,
      'fa-cog': !props.isNew
    })}
    title={trans(props.isNew ? 'new_slide' : 'slide_parameters', {}, 'slideshow')}
    subtitle={get(props.slide, 'meta.title')}
    onEntering={() => {
      if (props.slide) {
        props.reset(props.slide)
      } else {
        props.reset(Object.assign({}, SlideTypes.defaultProps, {id: makeId()}), true)
      }
    }}
  >
    <FormData
      level={5}
      name={selectors.STORE_NAME}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'type',
              label: trans('type'),
              type: 'choice',
              required: true,
              //calculated: (slide) => slide.content slide.type || (slideslide.mimeType.s
              options: {
                condensed: true,
                choices: {
                  image: trans('image')/*,
                  video: trans('video')*/
                }
              },
              linked: [
                {
                  name: 'content',
                  type: 'file',
                  label: trans('file'),
                  hideLabel: true,
                  required: true,
                  displayed: (slide) => -1 !== ['image', 'video'].indexOf(slide.type),
                  options: {
                    types: ['image/*']
                  }
                }
              ]
            }
          ]
        }, {
          icon: 'fa fa-fw fa-circle-info',
          title: trans('information'),
          fields: [
            {
              name: 'meta.title',
              label: trans('title'),
              type: 'string'
            }, {
              name: 'meta.description',
              label: trans('description'),
              type: 'string',
              options: {
                long: true
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'display.color',
              label: trans('color'),
              type: 'color'
            }
          ]
        }
      ]}
    />

    <Button
      className="modal-btn btn"
      type={CALLBACK_BUTTON}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.formData)
        props.fadeModal()
      }}
      primary={true}
    />
  </Modal>

SlideModal.propTypes = {
  // the slide to edit (original data)
  slide: T.shape(
    SlideTypes.propTypes
  ),
  isNew: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  // the current data of the form (aka the modified slide)
  formData: T.shape(
    SlideTypes.propTypes
  ).isRequired,
  reset: T.func.isRequired,
  save: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  SlideModal
}
