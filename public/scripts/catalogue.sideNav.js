const searchInputSidenav = document.getElementById('search-input-sidenav');
const sidenavOptions = document.querySelectorAll('#sidenav-3 li .sidenav-link');
const sidenavItems = document.querySelectorAll('.sidenav-categorie');

searchInputSidenav.addEventListener('input', () => {
    console.log(sidenavOptions);
    const filter = searchInputSidenav.value.toLowerCase();
    showSidenavOptions();
    const valueExist = !!filter.length;

    if (valueExist) {
        sidenavItems.forEach((el) => {
            const elText = el.textContent.trim().toLowerCase();
            const isIncluded = elText.includes(filter);
            if (!isIncluded) {
                el.style.display = 'none';
            }
        });
    }
});

const showSidenavOptions = () => {
    sidenavItems.forEach((el) => {
        el.style.display = 'flex';
    });
};