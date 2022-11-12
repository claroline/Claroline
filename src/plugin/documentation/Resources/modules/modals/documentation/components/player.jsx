import React from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'
import get from 'lodash/get'

import {scrollTo} from '#/main/app/dom/scroll'
import {theme} from '#/main/theme/config'
import {Router, Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'

import {flattenChapters} from '#/plugin/lesson/resources/lesson/utils'
import {Chapter} from '#/plugin/lesson/resources/lesson/components/chapter'
import {DocumentationSummary} from '#/plugin/documentation/modals/documentation/components/summary'

const DocumentationPlayer = (props) =>
  <div className="modal-body" style={{paddingTop: 0, paddingBottom: 0}}>
    <Helmet>
      <link rel="stylesheet" type="text/css" href={theme('claroline-distribution-plugin-lesson-lesson-resource')} />
    </Helmet>

    <Router embedded={true}>
      <Routes
        redirect={[
          {from: '/', exact: true, to: '/'+get(props.tree, 'children[0].slug'), disabled: !get(props.tree, 'children[0]')}
        ]}
        routes={[
          {
            path: '/summary',
            render() {
              return (
                <DocumentationSummary
                  path=""
                  tree={props.tree}
                />
              )
            }
          }, {
            path: '/:slug',
            render(routeProps) {
              const chapters = flattenChapters(props.tree.children || [])
              const chapter = chapters.find(c => routeProps.match.params.slug === c.slug)
              if (chapter) {
                return (
                  <Chapter
                    path=""
                    treeData={props.tree}
                    chapter={chapter}
                    backAction={{
                      type: LINK_BUTTON,
                      target: '/summary'
                    }}
                    onNavigate={() => scrollTo('.modal-content')}
                  />
                )
              }

              // chapter not found, redirect to the first chapter
              routeProps.history.push('/')

              return null
            }
          }
        ]}
      />
    </Router>
  </div>

DocumentationPlayer.propTypes = {
  tree: T.object.isRequired
}

export {
  DocumentationPlayer
}
