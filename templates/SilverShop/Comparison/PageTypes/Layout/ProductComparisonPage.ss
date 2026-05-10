<div class="silvershop-product-comparison">
    <% if $Comp %>
        <div class="silvershop-product-comparison__table">
            <table class="silvershop-product-comparison__grid">
                <thead class="silvershop-product-comparison__head">
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--remove"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--remove"><a class="silvershop-product-comparison__remove-link" href="$CompareRemoveLink">remove</a></td><% end_loop %></tr>
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--image"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--image"><img class="silvershop-product-comparison__image" src="$Image.getThumbnail.URL" alt="<%t SilverShop\Page\Product.ImageAltText "{Title} image" Title=$Title %>" /></td><% end_loop %></tr>
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--title"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--title"><a class="silvershop-product-comparison__title-link" href="$link">$Title</a></td><% end_loop %></tr>
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--price"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--price">$Price</td><% end_loop %></tr>
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--add"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--add"><a class="silvershop-product-comparison__add-link" href="$AddLink">Add to cart</a></td><% end_loop %></tr>
                </thead>
                <tbody class="silvershop-product-comparison__body">
                    <% loop $Features %>
                        <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--feature">
                            <th class="silvershop-product-comparison__cell silvershop-product-comparison__cell--feature">$Title</th>
                            <% loop $Up.ValuesForFeature($ID) %>
                                <td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--feature-value"><% include SilverShop\Comparison\Includes\TypedValue %></td>
                            <% end_loop %>
                        </tr>
                    <% end_loop %>
                </tbody>
                <tfoot class="silvershop-product-comparison__foot">
                    <tr class="silvershop-product-comparison__row silvershop-product-comparison__row--add"><td></td><% loop $Comp %><td class="silvershop-product-comparison__cell silvershop-product-comparison__cell--add"><a class="silvershop-product-comparison__add-link" href="$AddLink">Add to cart</a></td><% end_loop %></tr>
                </tfoot>
            </table>
        </div>
    <% else %>
        <div class="silvershop-product-comparison__empty">
            <h2 class="silvershop-product-comparison__empty-heading">Looks like you haven&#8299;t got any products to compare..</h2>
            <p class="silvershop-product-comparison__empty-text">To get started first click the <strong>Compare</strong> button on product in the store.</p>
        </div>
    <% end_if %>
</div>
