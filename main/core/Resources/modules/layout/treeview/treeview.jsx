import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

class TreeNode extends Component {
  constructor(props) {
    super(props)
    const opened = []
    const flattened= this.flatten(this.props.data)

    flattened.forEach(el => {
      if (this.hasChildChecked(el)) {
        opened.push(el.id)
      }
    })

    const cssClasses = this.props.options.cssClasses || {}
    this.commonCss = cssClasses.common || 'treeview-button-common'
    this.openCss = cssClasses.open || 'fa fa-fw fa-chevron-circle-down'
    this.closeCss = cssClasses.close || 'fa fa-fw fa-chevron-circle-right'

    this.state = { opened }
  }

  isChecked(el) {
    return this.props.options.selected.find(select => select.id === el.id) ? true: false
  }

  flatten(array) {
    let flattened = []

    array.forEach(el => {
      flattened.push(el)
      if (Array.isArray(el.children)) {
        flattened = flattened.concat(this.flatten(el.children))
      }
    })

    return flattened
  }

  //this is not really optimized yet but I guess that's okay
  hasChildChecked(el) {
      //no child, no point looking
    if (el.children.length === 0) return false

    return this.props.options.selected.find(select => {
      let found = false
      if (el.children && el.children.length > 0) {
        el.children.forEach(child => {
          if (child.id === select.id) found = true
          if (!found) found = this.hasChildChecked(child)
        })
      }
      return found
    }) ? true: false
  }

  isNodeOpen(el) {
    return this.state.opened.find(openNode => openNode === el.id) ? true: false
  }

  onExpandNode(el) {
    //this or setState for the update
    if (!this.isNodeOpen(el)) {
      this.props.onOpenNode(el)
      const opened = this.state.opened
      opened.push(el.id)
      this.setState({opened})
    } else {
      let opened = this.state.opened
      opened.splice(opened.findIndex(openNode => openNode === el.id), 1)
      this.setState({opened})
      this.props.onCloseNode(el)
    }
  }

  render() {
    return (
      <ul>
        {this.props.data.map(el =>
          (
            <li key={el.id}>
              {el.children.length > 0 &&
                <a
                  onClick={() => this.onExpandNode(el)}
                  className={classes({
                    [this.commonCss]: true,
                    [this.openCss]: this.isNodeOpen(el),
                    [this.closeCss]: !this.isNodeOpen(el)}
                  )}
                />
              }
              {this.props.options.selectable &&
                <input type='checkbox'
                  defaultChecked={this.isChecked(el)}
                  name={this.props.options.name + '[]'} value={el.id}
                  onChange={() => this.props.onChange(el)}
                />
              }
              <span className='treeview-content'>{this.props.render(el)}</span>
              <div
                className={classes({'treeview-hidden': !this.isNodeOpen(el)})}
                id={this.props.anchorPrefix + '-node-' + el.id}
              >
                <TreeNode
                  anchorPrefix={this.props.anchorPrefix}
                  data={el.children}
                  options={this.props.options}
                  onChange={this.props.onChange}
                  onOpenNode={this.props.onOpenNode}
                  onCloseNode={this.props.onCloseNode}
                  render={this.props.render}
                />
              </div>
            </li>)
          )
        }
      </ul>
    )
  }
}

class TreeView extends Component {
  constructor(props) {
    super(props)
  }

  render() {
    return(
      <div className="treeview">
        <TreeNode {...this.props} />
      </div>
    )
  }
}

TreeView.propTypes = {
  anchorPrefix: T.string,
  data: T.arrayOf(T.object).isRequired, //the datatree
  render: T.func, //custom renderer function
  options: T.shape({
    name: T.string, //checkbox base name
    selected: T.array,
    selectable: T.bool, //allow checkbox selection
    collapse: T.bool, //collapse the datatree
    cssClasses: {
      open: T.string, //default css for open node
      close: T.string, //default css for closed node
      common: T.string  //common css for button node
    }
  }),
  onChange: T.func, //callback for when a node is changed (open or closed)
  onOpenNode: T.func, //callback for when a node is opened
  onCloseNode: T.func //callback for when a node is closed
}

TreeNode.propTypes = TreeView.propTypes

TreeNode.defaultProps = {
  anchorPrefix: 'default',
  render: (el) => el.name,
  options: {
    selectable: false,
    collapse: true
  },
  onChange: () => {},
  onOpenNode: () => {},
  onCloseNode: () => {}
}

export {TreeView}
