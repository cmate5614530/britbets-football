<div class="bbfootball-bookie-form">
  <table class="form-table">
    <tr valign="top" class="">
      <th scope="row">
        <label>Affiliate URL:</label>
      </th>
      <td>
        <textarea class="large-text code" rows="10" name="affiliate_url"><?php echo get_post_meta($post->ID, 'affiliate_url', true); ?></textarea>
      </td>
    </tr>
  </table>
</div>
