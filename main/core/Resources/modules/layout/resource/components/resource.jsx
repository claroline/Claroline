import React, {PropTypes as T} from 'react'

import { Page, PageHeader, PageContent } from '#/main/core/layout/page/components/page.jsx'
import { ResourceActions } from './resource-actions.jsx'

const Resource = props =>
  <Page
    className="resource-page"
    embedded={props.embedded}
    fullscreen={props.fullscreen}

    modal={props.modal}
    showModal={props.showModal}
    fadeModal={props.fadeModal}
    hideModal={props.hideModal}
  >
    <PageHeader
      className="resource-header"
      title={props.resourceNode.name}
    >
      <ResourceActions
        resourceNode={props.resourceNode}
        editMode={props.editMode}
        edit={props.edit}
        save={props.save}
        customActions={props.customActions}
        fullscreen={props.fullscreen}
        toggleFullscreen={props.toggleFullscreen}
        togglePublication={props.togglePublication}
        showModal={props.showModal}
      />
    </PageHeader>

    <PageContent>
      {props.children}
    </PageContent>
  </Page>

Resource.propTypes = {
  resourceNode: T.shape({
    name: T.string.isRequired
  }).isRequired,
  fullscreen: T.bool,
  embedded: T.bool,
  children: T.node.isRequired,
  modal: T.shape({
    type: T.string,
    fading: T.bool.isRequired,
    props: T.object.isRequired
  }),
  showModal: T.func.isRequired,
  fadeModal: T.func.isRequired,
  hideModal: T.func.isRequired,
  toggleFullscreen: T.func.isRequired,
  togglePublication: T.func.isRequired,

  customActions: T.array.isRequired,
  editMode: T.bool,
  edit: T.oneOfType([T.func, T.string]).isRequired,
  save: T.object.isRequired
}

Resource.defaultProps = {
  embedded: false
}

export {Resource}
