function UnsafeFilter($sce) {
    return $sce.trustAsHtml;
}

export default UnsafeFilter
