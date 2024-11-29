<!-- Carrossel de Parceiros -->
<div class="parceiros-carousel owl-carousel">
    <?php 
        // Consulta para buscar parceiros pelo CEP
        $sql_parceiros = "SELECT * FROM meus_parceiros WHERE id = $idParceiro && status = 'ATIVO'";
        $result_parceiros = $mysqli->query($sql_parceiros) or die($mysqli->error);

        if ($result_parceiros->num_rows > 0): 
            while ($parceiro = $result_parceiros->fetch_assoc()): 
                // Exibe cada parceiro no carrossel
                //$logoParceiro = !empty($parceiro['logo']) ? $parceiro['logo'] : 'placeholder.jpg'; 

                // Consulta para buscar categorias únicas dos produtos do parceiro
                $sql_categorias = "SELECT DISTINCT categoria FROM produtos WHERE id_parceiro = ".$parceiro['id'];
                $result_categorias = $mysqli->query($sql_categorias) or die($mysqli->error);
    ?>
    <div class="parceiro-card">
        <!-- Exibe as categorias de produtos do parceiro -->
        <div class="categorias-parceiro">
            <?php if ($result_categorias->num_rows > 0): ?>
                <?php while ($categoria = $result_categorias->fetch_assoc()): ?>
                    <p><?php echo htmlspecialchars($categoria['categoria']); ?></p>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Sem categorias</p>
            <?php endif; ?>
        </div>
    </div>

    <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhum parceiro ativo no momento.</p>
    <?php endif; ?>
</div>








