import React, {useCallback} from 'react'
import {useDispatch, useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {EditorPage} from '#/main/app/editor'
import {actions as formActions} from '#/main/app/content/form'
import {selectors as resourceSelectors} from '#/main/core/resource'

import {selectors} from '#/plugin/slideshow/resources/slideshow/editor/store'
import {MODAL_SLIDE} from '#/plugin/slideshow/resources/slideshow/editor/modals/slide'
import {Slides} from '#/plugin/slideshow/resources/slideshow/components/slides'

const SlideshowEditorContent = () => {
  const slides = useSelector(selectors.slides)

  const dispatch = useDispatch()
  const updateProp = useCallback((prop, value) => {
    dispatch(formActions.updateProp(resourceSelectors.EDITOR_NAME, 'resource.'+prop, value))
  }, [resourceSelectors.EDITOR_NAME])

  return (
    <EditorPage
      title={trans('slides', {}, 'slideshow')}
      help={trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.')}
    >
      {0 === slides.length &&
        <ContentPlaceholder
          className="mb-3"
          size="lg"
          icon="fa fa-image"
          title={trans('no_slide', {}, 'slideshow')}
        />
      }

      {0 !== slides.length &&
        <Slides
          className="mb-3"
          slides={slides}
          actions={(slide) => [
            {
              name: 'edit',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              modal: [MODAL_SLIDE, {
                slide: slide,
                save: (updated) => {
                  const updatedPos = slides.findIndex(current => current.id === updated.id)

                  const newSlides = slides.slice(0)
                  newSlides[updatedPos] = updated

                  updateProp('slides', newSlides)
                }
              }]
            }, {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              callback: () => {
                const deletedPos = slides.findIndex(current => current.id === slide.id)
                if (-1 !== deletedPos) {
                  const newSlides = slides.slice(0)
                  newSlides.splice(deletedPos, 1)

                  updateProp('slides', newSlides)
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
        className="btn btn-primary w-100"
        type={MODAL_BUTTON}
        primary={true}
        size="lg"
        label={trans('add_slide', {}, 'slideshow')}
        modal={[MODAL_SLIDE, {
          save: (slide) => updateProp('slides', [].concat(slides, [slide]))
        }]}
      />
    </EditorPage>
  )
}

export {
  SlideshowEditorContent
}
