import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {SummarizedContent} from '#/main/app/content/summary/components/content'
import {LINK_BUTTON} from '#/main/app/buttons'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {Directory as DirectoryTypes} from '#/main/core/resources/directory/prop-types'
import {CurrentDirectory} from '#/main/core/resource/explorer/components/current'

const ResourceExplorer = props => {
  function summaryLink(directory) {
    return {
      type: LINK_BUTTON,
      id: directory.id,
      icon: directory._opened ? 'fa fa-fw fa-folder-open' : 'fa fa-fw fa-folder',
      label: directory.name,
      collapsed: !directory._opened,
      collapsible: !directory._loaded || (directory.children && 0 !== directory.children.length),
      toggleCollapse: (collapsed) => props.toggleDirectoryOpen(directory, !collapsed),
      target: `/${directory.id}`,
      children: directory.children ? directory.children.map(summaryLink) : []
    }
  }

  return (
    <SummarizedContent
      className="resources-explorer"
      summary={{
        displayed: isEmpty(props.currentConfiguration) || props.currentConfiguration.display.showSummary,
        opened: !isEmpty(props.currentConfiguration) && props.currentConfiguration.display.openSummary,
        title: trans('directories'),
        links: props.directories.map(summaryLink)
      }}
    >
      <Routes
        redirect={props.root ? [
          {from: '/', exact: true, to: `/${props.root.id}`}
        ] : undefined}
        routes={[
          {
            path: props.root ? '/:id' : '/:id?',
            onEnter: (params = {}) => props.changeDirectory(params.id),
            render: () => {
              const Current =
                <CurrentDirectory
                  name={props.name}
                  primaryAction={props.primaryAction}
                  actions={props.actions}
                  currentId={props.currentId}
                  listConfiguration={!isEmpty(props.currentConfiguration) ? props.currentConfiguration.list : undefined}
                />

              return Current
            }
          }
        ]}
      />
    </SummarizedContent>
  )
}

ResourceExplorer.propTypes = {
  name: T.string.isRequired,
  primaryAction: T.func,
  root: T.shape(
    ResourceNodeTypes.propTypes
  ),
  currentId: T.string,
  currentConfiguration: T.shape(
    DirectoryTypes.propTypes
  ),
  directories: T.arrayOf(T.shape(
    ResourceNodeTypes.propTypes
  )),
  toggleDirectoryOpen: T.func.isRequired,
  changeDirectory: T.func.isRequired,
  actions: T.func
}

ResourceExplorer.defaultProps = {
  currentConfiguration: {},
  directories: []
}

export {
  ResourceExplorer
}
