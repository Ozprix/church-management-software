<template>
  <div>
    <tax-receipt-page />
  </div>
</template>

<script>
import TaxReceiptPage from '../../components/finance/tax-receipts/TaxReceiptPage.vue';

export default {
  name: 'TaxReceiptListPage',
  components: {
    TaxReceiptPage
  }
};
</script>
