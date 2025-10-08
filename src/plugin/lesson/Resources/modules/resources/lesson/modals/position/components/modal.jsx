import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {FormModal} from '#/main/app/data/modals/form/components/modal'

import {selectors} from '#/plugin/lesson/resources/lesson/modals/position/store/selectors'

const PositionModal = props => {
  const parentChoices = props.pages
    .filter(page => page.slug !== props.page.slug)
    .reduce((pageChoices, current) => Object.assign(pageChoices, {
      [current.slug]: current.title
    }), {})

  const pageChoices = props.pages
    // only display the subpages of the selected `parent`
    .filter(page => page.slug !== props.page.slug
      && (get(page, 'parentSlug') === props.positionData.parent || (!props.positionData.parent && get(page, 'parentSlug') === props.root.slug))
    )
    .reduce((pageChoices, current) => Object.assign(pageChoices, {
      [current.slug]: current.title
    }), {})

  // convert current page position to display in form
  const currentPosition = {}

  // get parent
  if (props.page.parentSlug) {
    currentPosition.parent = props.page.parentSlug
  }

  // get position between current parent children
  const siblings = props.pages.filter(page => get(page, 'parentSlug') === get(props.page, 'parentSlug'))
  const siblingIndex = siblings.findIndex(page => page.slug === props.page.slug)
  if (1 === siblings.length || 0 === siblingIndex) {
    // first or only child
    currentPosition.order = 'first'
  } else if (siblings.length === siblingIndex + 1) {
    // last child
    currentPosition.order = 'last'
  } else {
    currentPosition.order = 'after'
    currentPosition.page = siblings[siblingIndex - 1].slug
  }

  return (
    <FormModal
      {...omit(props, 'page', 'pages', 'positionData', 'update')}
      subtitle={props.page.title}
      data={currentPosition}
      isNew={false}
      name={selectors.STORE_NAME}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'parent',
              label: trans('parent'),
              type: 'choice',
              placeholder: trans('root'),
              options: {
                condensed: true,
                choices: parentChoices
              },
              onChange: () => {
                props.update('order', 'last')
                props.update('page', null)
              }
            }, {
              name: 'order',
              label: trans('position'),
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                noEmpty: true,
                choices: isEmpty(pageChoices) ? {
                  first: trans('first')
                } : {
                  first: trans('first'),
                  before: trans('before'),
                  after: trans('after'),
                  last: trans('last')
                }
              },
              onChange: (order) => {
                if (-1 !== ['first', 'last'].indexOf(order)) {
                  props.update('page', null)
                } else if (!props.positionData.page) {
                  // auto select a page
                  const siblings = Object.keys(pageChoices)
                  if (!isEmpty(siblings)) {
                    let page = siblings[siblings.length - 1]
                    if ('before' === order) {
                      page = siblings[0]
                    }

                    props.update('page', page)
                  }
                }
              },
              linked: [
                {
                  name: 'page',
                  label: trans('page', {}, 'lesson'),
                  type: 'choice',
                  required: true,
                  hideLabel: true,
                  displayed: (position) => position.order && -1 === ['first', 'last'].indexOf(position.order),
                  options: {
                    condensed: true,
                    noEmpty: true,
                    choices: pageChoices
                  }
                }
              ]
            }
          ]
        }
      ]}
    />
  )
}

PositionModal.propTypes = {
  title: T.string,
  root: T.shape({
    title: T.string.isRequired,
    slug: T.string.isRequired
  }),
  page: T.shape({
    title: T.string.isRequired,
    slug: T.string.isRequired,
    parentSlug: T.string
  }),
  pages: T.arrayOf(T.shape({
    title: T.string.isRequired,
    slug: T.string.isRequired,
    parentSlug: T.string
  })),
  positionData: T.shape({
    parent: T.string,
    order: T.oneOf(['first', 'before', 'after', 'last']),
    page: T.string
  }),
  onSave: T.func.isRequired,
  saveLabel: T.string.isRequired,
  update: T.func.isRequired,
  fadeModal: T.func.isRequired
}

PositionModal.defaultProps = {
  pages: []
}

export {
  PositionModal
}
