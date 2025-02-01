jQuery(document).ready(function($) {
    $('.otd-carousel-container').each(function() {
        const container = $(this);
        const carousel = container.find('.otd-carousel');
        const list = carousel.find('.otd-post-list');
        const items = list.find('li');
        const prev = container.find('.prev');
        const next = container.find('.next');
        
        let currentPosition = 0;
        const itemWidth = 270; // 250px width + 20px gap
        const visibleItems = Math.floor(carousel.width() / itemWidth);
        const maxPosition = Math.max(0, items.length - visibleItems);

        function updateButtons() {
            prev.prop('disabled', currentPosition <= 0);
            next.prop('disabled', currentPosition >= maxPosition);
        }

        function slide(direction) {
            if (direction === 'prev' && currentPosition > 0) {
                currentPosition--;
            } else if (direction === 'next' && currentPosition < maxPosition) {
                currentPosition++;
            }
            
            list.css('transform', `translateX(-${currentPosition * itemWidth}px)`);
            updateButtons();
        }

        prev.on('click', () => slide('prev'));
        next.on('click', () => slide('next'));

        // Initial button state
        updateButtons();

        // Update on window resize
        $(window).on('resize', function() {
            const newVisibleItems = Math.floor(carousel.width() / itemWidth);
            const newMaxPosition = Math.max(0, items.length - newVisibleItems);
            currentPosition = Math.min(currentPosition, newMaxPosition);
            list.css('transform', `translateX(-${currentPosition * itemWidth}px)`);
            updateButtons();
        });
    });
}); 