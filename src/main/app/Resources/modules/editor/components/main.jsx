import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'
import omit from 'lodash/omit'
import Modal from 'react-bootstrap/Modal'

import {trans} from '#/main/app/intl'
import {Routes} from '#/main/app/router'
import {Toolbar} from '#/main/app/action'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Form, selectors as formSelectors} from '#/main/app/content/form'

import {EditorMenu} from '#/main/app/editor/components/menu'
import {EditorPage} from '#/main/app/editor/components/page'
import {AppLoader} from '#/main/app/layout/components/loader'
import {useHistory} from 'react-router-dom'

const Editor = (props) => {
  const formData = useSelector((state) => formSelectors.data(formSelectors.form(state, props.name)))

  const pages = (props.overview ? [
    {
      name: 'overview',
      title: trans('overview'),
      component: props.overview
    }
  ] : []).concat(props.pages.filter(page => !page.disabled))

  const history = useHistory()

  return (
    <Modal
      show={true}
      fullscreen={true}
      className="app-editor"
      animation={false}
      backdrop={false}
      onHide={() => {
        // TODO : check pending changes
        history.push(props.close)
      }}
    >
      <AppLoader />
      <EditorMenu
        path={props.path}
        title={get(formData, 'name') || props.title}
        pages={pages}
        actions={props.actions}
      />

      <div className="app-editor-body" role="presentation">
        <Form
          className="app-editor-form"
          name={props.name}
          target={props.target}
          onSave={props.onSave}
          buttons={true}
        >
          <Routes
            path={props.path}
            redirect={props.defaultPage ? [
              {from: '/', exact: true, to: '/' + props.defaultPage}
            ] : undefined}
            routes={pages.map(page => ({
              path: '/' + page.name,
              ...omit(page, 'component', 'render'),
              render: (routerProps) => (
                <EditorPage
                  {...omit(page, 'component', 'render')}
                >
                  {page.component ? createElement(page.component) : page.render(routerProps)}
                </EditorPage>
              )
            }))}
          />
        </Form>

        <Toolbar
          className="app-editor-toolbar sticky-top"
          buttonName="btn btn-text-body"
          tooltip="left"
          actions={[
            {
              name: 'close',
              label: trans('close'),
              icon: 'fa fa-fw fa-times',
              type: LINK_BUTTON,
              target: props.close,
              exact: true
            }, {
              name: 'preview',
              label: trans('preview'),
              icon: 'fa fa-fw fa-eye',
              type: LINK_BUTTON,
              target: props.path,
              exact: true
            }, {
              name: 'summary',
              label: trans('summary'),
              icon: 'fa fa-fw fa-list',
              type: LINK_BUTTON,
              target: props.path,
              exact: true
            }
          ]}
        />
      </div>
    </Modal>
  )
}

Editor.propTypes = {
  path: T.string.isRequired,

  /**
   * The name of the editor store we will connect to.
   */
  name: T.string.isRequired,
  target: T.oneOfType([
    // a plain URL
    T.string,
    // a symfony route
    T.array,
    // a func to generate the target of the form
    // it receives the form data and the isNew flag has params.
    T.func
  ]).isRequired,
  onSave: T.func,
  close: T.oneOfType([
    // a plain URL
    T.string,
    // a symfony route
    T.array
  ]),

  title: T.string,
  overview: T.any,
  pages: T.arrayOf(T.shape({
    name: T.string.isRequired,
    title: T.string.isRequired,
    disabled: T.bool,
    component: T.elementType,
    render: T.func
  })),
  defaultPage: T.string,
  actions: T.arrayOf(T.shape({}))
}

Editor.defaultProps = {
  pages: T.string,
  actions: []
}

export {
  Editor
}
