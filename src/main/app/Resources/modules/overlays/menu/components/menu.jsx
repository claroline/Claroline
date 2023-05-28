/**
 * Just reexport react-bootstrap components to avoid hard dep to the library.
 * Most of the time, Dropdown are manipulated through MenuButton|MENU_BUTTON.
 */

import Dropdown from 'react-bootstrap/Dropdown'

const Menu = Dropdown.Menu
Menu.defaultProps = {
  renderOnMount: false
}

const MenuToggle = Dropdown.Toggle
const MenuItem = Dropdown.Item
const MenuText = Dropdown.ItemText
const MenuHeader = Dropdown.Header
const MenuDivider = Dropdown.Divider

export {
  Menu,
  MenuToggle,
  MenuItem,
  MenuHeader,
  MenuText,
  MenuDivider
}
