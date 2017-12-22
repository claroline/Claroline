import {t} from '#/main/core/translation'

const OrganizationCard = (row) => ({
  icon: 'fa fa-building',
  title: row.name,
  subtitle: row.code,
  flags: [
    row.meta.default && ['fa fa-check', t('default')]
  ].filter(flag => !!flag)
})

export {
  OrganizationCard
}
