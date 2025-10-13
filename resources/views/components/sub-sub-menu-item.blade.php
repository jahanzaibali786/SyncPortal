@props(['text'])
<style>
    .sub-menu-dropdown {
    margin-bottom: 5px;
}

.sub-menu-dropdown .dropdown-header {
    cursor: pointer;
    padding-left: 20px;
}

.sub-menu-dropdown .dropdown-content {
    display: none;
    padding-left: 10px;
}

.sub-menu-dropdown.active .dropdown-content {
    display: block;
}

.sub-menu-dropdown .bi-chevron-down {
    transition: transform 0.3s ease;
}

.sub-menu-dropdown.active .bi-chevron-down {
    transform: rotate(180deg);
}

</style>
<div class="sub-menu-dropdown">
    <div class="dropdown-header d-flex align-items-center">
        <span class="f-14 text-lightest text-bold">{{ $text }}</span>
        <i class="bi bi-chevron-down ml-auto"></i>
    </div>
    <div class="dropdown-content">
        {{ $slot }}
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.sub-menu-dropdown .dropdown-header');

    dropdowns.forEach(header => {
        header.addEventListener('click', function(e) {
            const parent = this.closest('.sub-menu-dropdown');
            
            // Find the chevron inside this header, regardless of what was clicked
            const chevron = this.querySelector('.bi.bi-chevron-down, .bi.bi-chevron-up');
            // console.log(chevron);
            
            parent.classList.toggle('active');

            if (parent.classList.contains('active')) {
                if (chevron) {
                    chevron.classList.remove('bi-chevron-down');
                    chevron.classList.add('bi-chevron-up');
                }
            } else {
                if (chevron) {
                    chevron.classList.remove('bi-chevron-up');
                    chevron.classList.add('bi-chevron-down');
                }
            }
        });
    });
});
</script>

