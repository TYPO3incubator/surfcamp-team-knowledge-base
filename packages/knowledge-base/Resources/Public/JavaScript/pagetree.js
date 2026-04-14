function initTree() {
    document.querySelectorAll('.item-childs').forEach(el => { el.hidden = true; });
    document.querySelectorAll('.kb-btn-collapse').forEach(btn => { btn.classList.add('is-collapsed'); });
}

initTree();
window.addEventListener('pageshow', initTree);

document.addEventListener('click', e => {
    const button = e.target.closest('.kb-btn-collapse');
    if (!button) return;

    e.stopPropagation();

    const wrapper = button.closest('.item-wrapper');
    const children = wrapper?.querySelector(':scope > .item-childs');

    if (children) {
        const isCollapsed = button.classList.toggle('is-collapsed');
        children.hidden = isCollapsed;
    }
});
